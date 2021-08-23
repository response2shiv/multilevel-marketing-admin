<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixCalculateDownlineQvWithTsaFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP FUNCTION IF EXISTS get_rank_by_percentage(character varying, bigint, numeric, bigint)');

        DB::statement('
CREATE OR REPLACE FUNCTION public.get_rank_by_percentage(
	dist_id character varying,
	rankdefid bigint,
	ranklimit numeric,
	qualifyqv bigint,
	active_left numeric,
	active_right numeric
	)
    RETURNS rank
    LANGUAGE \'plpgsql\'

    COST 100
    VOLATILE 
AS $BODY$DECLARE 
 	cur_rank rank;
 	rank_qv bigint;
	startdate_of_month timestamp;
    enddate_of_month timestamp;
	pqv numeric;
 BEGIN
    --RAISE NOTICE \'QUALIFY QV %\',qualifyqv;
	--RAISE NOTICE \'RANK LIMIT %\',ranklimit;
	
	SELECT sum(
		CASE WHEN COALESCE(current_month_qv, 0) > qualifyqv*ranklimit 
			THEN qualifyqv*ranklimit 
		ELSE COALESCE(current_month_qv, 0) 
		END) INTO rank_qv
	FROM users WHERE sponsorid = dist_id;
	
	--RAISE NOTICE \'RANK QV %\',rank_qv;
	
	-- first date of month
    SELECT date_trunc(\'month\', NOW())::DATE into startdate_of_month;
    -- last date of month
    SELECT (date_trunc(\'month\', NOW())::DATE + interval \'1 month - 1 millisecond\')::DATE into enddate_of_month;
	
	SELECT COALESCE(SUM(o.orderqv), 0) into pqv
        FROM users u
        JOIN orders o
        ON u.id = o.userid
        WHERE o.created_dt >= startdate_of_month
            AND o.created_dt <= enddate_of_month
            AND (o.statuscode = 1 OR o.statuscode = 6)
            AND u.distid = dist_id;

	rank_qv = rank_qv + pqv;

	SELECT rankval,rankdesc,rank_qv 
	FROM rank_definition INTO cur_rank  
	WHERE
		rank_qv >= min_qv
		AND id = rankdefid
		AND active_left >= min_binary_count
		AND active_right >= min_binary_count
		LIMIT 1;
	
	--RAISE NOTICE \'RANK VALUE %\',cur_rank.rankval;
	
	RETURN cur_rank;
		
END;$BODY$;
        ');

        DB::statement('
CREATE OR REPLACE FUNCTION public.check_binary_verification(
	left_key integer,
	right_key integer)
    RETURNS bigint
    LANGUAGE \'sql\'

    COST 100
    VOLATILE 
AS $BODY$
     SELECT count(u.id) FROM users u 
        JOIN binary_plan bp
        ON bp.user_id = u.id
        WHERE (
               u.id IN (
                    SELECT userid FROM orders
                    WHERE date(created_dt) >= NOW() - interval \'1 month\' GROUP BY userid HAVING sum(orderqv) >= 100
                    )
                    or (u.current_product_id = 16 and u.created_dt >= date_trunc(\'month\', NOW())::DATE - interval \'1 year\')
                    or u.distid in (\'A1357703\', \'A1637504\', \'TSA9846698\',
                    \'TSA3564970\',
                    \'TSA9714195\',
                    \'TSA8905585\',
                    \'TSA2593082\',
                    \'TSA0707550\',
                    \'TSA9834283\',
                    \'TSA5138270\',
                    \'TSA8715163\')
                   )
                   and u.account_status not in (\'TERMINATED\', \'SUSPENDED\')
                   and _lft >= left_key
                   AND _rgt <= right_key
                   ;
        $BODY$;     
        ');

        DB::statement('
CREATE OR REPLACE FUNCTION public.calculate_downline_qv_with_tsa(
	)
    RETURNS void
    LANGUAGE \'plpgsql\'

    COST 100
    VOLATILE 
AS $BODY$DECLARE 
             startdate_of_month timestamp;
             enddate_of_month timestamp;
             transac   RECORD;
             sponsor RECORD;
             cuser RECORD;
             rdef RECORD;
             sp_id varchar(100);
             counter integer := 0;
             rank_qv integer := 0;
             rank_desc varchar(100);
             crank rank;
             rank_exists integer := 0;
             curr_month_qv bigint := 0;
             curr_month_pqv bigint := 0;
			 curr_month_tsa integer := 0;
			 curr_month_cv bigint := 0;
             qualify_qv bigint := 0;
             is_user_enabled integer := 0;
			 binary_id integer;
			 active_left numeric := 0;
			 active_right numeric := 0;
			 tbl_left RECORD;
			 tbl_right RECORD;
             
             --This variable is to sent inside rank history table Remarks field flaging how a user was promoted 
             --either by QV or False Promoted
             rank_remarks varchar(100);
             
             cur_transacs CURSOR (d1 date,d2 date)
                FOR SELECT * FROM vorder_product_qv where created_dt >= d1 and created_dt <= d2 + interval \'1 day - 1 millisecond\';
            
             cur_all_users CURSOR
                FOR SELECT id,distid,COALESCE(current_month_qv, 0) as current_month_qv,COALESCE(current_month_tsa, 0) as current_month_tsa,COALESCE(current_month_pqv, 0) as current_month_pqv, COALESCE(current_month_cv, 0) as current_month_cv,is_active FROM users where account_status not in (\'TERMINATED\', \'SUSPENDED\');
                
             cur_rank_defs CURSOR
                FOR SELECT * FROM rank_definition ORDER BY rankval;
                 
             BEGIN
            	-- temp table to store orders for QV calculation 
               TRUNCATE table "qv_transaction";
			   
               -- reset data to default for every user
               UPDATE users
			   SET current_month_pqv = 0,
			   		current_month_tsa=0,
					current_month_qv=0,
					current_month_rank=10,
					current_month_cv=0;
               
               -- first date of month
               SELECT date_trunc(\'month\', NOW())::DATE into startdate_of_month;
               -- last date of month
               SELECT (date_trunc(\'month\', NOW())::DATE + interval \'1 month - 1 day\')::DATE into enddate_of_month;
               
               -- clear the previous history of ranks for the current month
               delete from user_rank_history where "period" = enddate_of_month;
               delete from rank_history where created_dt >= startdate_of_month and created_dt <= enddate_of_month + interval \'1 day - 1 millisecond\';
			   
               
               -- Open the cursor cur_transacs
               --update all users PQV for the running month through and 20190528 fix pqv range from 1 month to -30 days
               ----update users set current_month_pqv=su.qv
               ----from (SELECT userid,sum(qv) as qv FROM vorder_product_qv 
               ----      where date(created_dt) between date_trunc(\'day\', NOW())::DATE - interval \'30 days\' and date_trunc(\'day\', NOW())::DATE + interval \' + 1 day - 1 second\' group by userid) su
               ----where users.id=su.userid;
               
               --TODO
               --accumulate all users\' customers PQV of respective users along with the users PQV               
           OPEN cur_transacs(startdate_of_month,enddate_of_month);    
           LOOP
                -- fetch row into the transac
                FETCH cur_transacs INTO transac;		
            
                -- exit when no more row to fetch
                EXIT WHEN NOT FOUND;	
            
                --a counter representing the upper level the sponsor
                counter := 1;				
            
                --go up the sponsor tree and add an QV entry to each user on the upline
                --pick the sponsor id of the user
                SELECT sponsorid INTO sp_id FROM public."users" WHERE id = transac.userid;        
                SELECT count(id) INTO is_user_enabled FROM users WHERE id = transac.userid AND account_status NOT IN (\'TERMINATED\', \'SUSPENDED\');
                
                IF is_user_enabled > 0 THEN
            		INSERT INTO qv_transaction (transaction_id, transaction_date, qv, user_id, level, initiated_user_id, cv)
                    VALUES (transac.orderid, transac.created_dt, transac.qv, transac.userid, counter, transac.userid, transac.cv);	
                END IF;                    
                    
                LOOP 
                    -- get the sponsor of the buyer, sponsor\'s sponsor and so on	
                    SELECT * FROM users INTO sponsor WHERE distid = sp_id;
                    -- exit the loop if there no longer a sponsor of the buyer or the sponsor
                    EXIT WHEN NOT FOUND; --or sponsor.id is null;	
                    
                    IF sponsor.account_status NOT IN (\'TERMINATED\', \'SUSPENDED\') THEN
	                   INSERT INTO qv_transaction (transaction_id, transaction_date, qv, user_id, level, initiated_user_id, cv)
                       VALUES (transac.orderid, transac.created_dt, transac.qv, sponsor.id, counter, transac.userid, transac.cv);
                    END IF;
							
                    sp_id := sponsor.sponsorid;
					
                    --add one to the level to which we are going to add  QV next
                    counter := counter + 1; 
                END LOOP ; 					
			END LOOP;
           -- Close the cursor cur_transacs
           CLOSE cur_transacs; 
               
			-- Update the current month qv of each user in the user table  
            UPDATE users SET current_month_qv = sq.totalqv, current_month_cv = sq.totalcv
            FROM (
				SELECT user_id,
				sum(qv) as totalqv,
				sum(cv) as totalcv 
				FROM qv_transaction
				group by user_id
			) as sq
            WHERE sq.user_id = users.id;
   
            -- Open the cursor all_users
            OPEN cur_all_users;       
               LOOP
                    -- fetch row into the cur_user
                    FETCH cur_all_users INTO cuser;
                    -- exit when no more row to fetch
                    EXIT WHEN NOT FOUND;	
            
					SELECT bp.id INTO binary_id FROM users u
					JOIN binary_plan bp
					ON bp.user_id = u.id
					WHERE u.distid = cuser.distid;
	
                    OPEN cur_rank_defs;
                    LOOP
                        FETCH cur_rank_defs INTO rdef;			
                        --exit when no more row to fetch
                        EXIT WHEN NOT FOUND;
						
						IF rdef.rankval >= 50 THEN
							-- get left and right active users
							SELECT * FROM get_user_subtree(\'L\', binary_id) INTO tbl_left;
							SELECT * FROM get_user_subtree(\'R\', binary_id) INTO tbl_right;

							SELECT check_binary_verification(tbl_left.left_key, tbl_left.right_key) INTO active_left;
							SELECT check_binary_verification(tbl_right.left_key, tbl_right.right_key) INTO active_right;						
						ELSE
							active_left = 5;
							active_right = 5;
						END IF;
                
						-- get rank if user has enough QV for it
                        SELECT * FROM get_rank_by_percentage(cuser.distid, rdef.id, rdef.rank_limit, rdef.min_qv, active_left, active_right) INTO crank;                        
                        rank_remarks:=\'QV\';	
                        
                        --RAISE NOTICE \'Rank Value calculated %\',crank.rankval;			
                        IF crank.rankval IS NOT NULL THEN
                            SELECT count(*) FROM rank_history INTO rank_exists 
                                WHERE users_id = cuser.id 
                                AND lifetime_rank = crank.rankval
                                AND created_dt >= startdate_of_month 
	                        	AND created_dt <= enddate_of_month + interval \'1 day - 1 millisecond\';
								
                            IF rank_exists < 1 THEN
                                INSERT INTO rank_history(users_id, lifetime_rank, created_dt, remarks)
								VALUES (cuser.id, crank.rankval, now(), rank_remarks);
                            END IF;
            
                            --take the total qv from users
                            SELECT current_month_qv, current_month_cv into curr_month_qv, curr_month_cv FROM users WHERE id = cuser.id;
                            
                            --Checking whether data already inserted to the user_rank_history table. 
                            SELECT count(*) FROM user_rank_history into rank_exists WHERE period = enddate_of_month and user_id = cuser.id;		  
							
                            IF rank_exists > 0 THEN
                            	--Data already available for a particular user for particular month, so update				
                                UPDATE public.user_rank_history 
                                SET monthly_rank = crank.rankval,
									monthly_rank_desc = crank.rankdesc,
									monthly_qv = curr_month_qv,
									qualified_qv = crank.rank_qv,
									qualified_tsa = 0,
									monthly_tsa = 0,
									monthly_cv = curr_month_cv
                                WHERE user_id=cuser.id AND period = enddate_of_month;		
                            ELSE
                            	--Data unavailable for a particular user for particular month, so insert
                                INSERT INTO public.user_rank_history(
									user_id,
									monthly_rank,
									monthly_rank_desc,
									period,
									monthly_qv,
									qualified_qv,
									qualified_tsa,
									monthly_tsa,
									monthly_cv
								) 
                                VALUES (
									cuser.id,
									crank.rankval,
									crank.rankdesc,
									enddate_of_month,
									curr_month_qv,
									crank.rank_qv,
									0,
									0,
									curr_month_cv
								);
                            END IF;	
            
                            --Updates the user table current rank 
                            UPDATE users SET current_month_rank = crank.rankval WHERE id = cuser.id;
						ELSE
							EXIT;
                        END IF;
                    END LOOP;
                    CLOSE cur_rank_defs;		
                    
                    SELECT count(*) FROM rank_history INTO rank_exists WHERE users_id=cuser.id;
                    IF rank_exists < 1 THEN
                        INSERT INTO rank_history(users_id, lifetime_rank, created_dt) VALUES (cuser.id, 10, now());
                    END IF;
               END LOOP;
               -- Close the cursor all_users
               CLOSE cur_all_users;      
               raise info \'Ended %\', now();
               
               --insert a record that this process ran on certain date and time
               truncate rank_log;
               insert into rank_log (worked_on) values (now());   
            END;$BODY$;
        ');

        DB::statement('
CREATE OR REPLACE FUNCTION public.calculate_month_downline_qv(
	from_date timestamp without time zone,
	to_date timestamp without time zone)
    RETURNS void
    LANGUAGE \'plpgsql\'

    COST 100
    VOLATILE 
AS $BODY$DECLARE 
             startdate_of_month timestamp;
             enddate_of_month timestamp;
             transac   RECORD;
             sponsor RECORD;
             cuser RECORD;
             rdef RECORD;
             sp_id varchar(100);
             counter integer :=0;
             rank_qv integer :=0;
             total_tsa integer :=0;
             rank_desc varchar(100);
             crank rank;
             crank2 tsarank;
             --tsarank type to get the tsa count and rankval
             trank tsarank;
             rank_exists integer :=0;
             curr_month_qv bigint:=0;
             curr_month_pqv bigint:=0;
			 curr_month_tsa integer:=0;
			 curr_month_cv bigint:=0;
             qualify_qv bigint:=0;
             is_user_enabled integer :=0;
			 binary_id integer;
			 active_left numeric := 0;
			 active_right numeric := 0;
			 tbl_left RECORD;
			 tbl_right RECORD;
             
                --This variable is to sent inside rank history table Remarks field flaging how a user was promoted 
               --either by QV or TSA Count or False Promoted
             rank_remarks varchar(100);
             
             cur_transacs CURSOR (d1 date,d2 date)
                FOR SELECT * FROM vorder_product_qv where created_dt >= d1 and created_dt <= d2 + interval \'1 day - 1 millisecond\';
            
             cur_all_users CURSOR
                FOR SELECT id,distid,COALESCE(current_month_qv, 0) as current_month_qv,COALESCE(current_month_tsa, 0) as current_month_tsa,COALESCE(current_month_pqv, 0) as current_month_pqv, COALESCE(current_month_cv, 0) as current_month_cv,is_active FROM users where account_status not in (\'TERMINATED\', \'SUSPENDED\');
                
             cur_rank_defs CURSOR
                FOR SELECT * FROM rank_definition ORDER BY rankval;
                 
             BEGIN
            
               
               TRUNCATE table "qv_transaction";
               --20190424 modified to update pqv=0 as every month during every time it is reset to 0
               update users set current_month_pqv=0,current_month_tsa=0,current_month_qv=0,current_month_rank=10,current_month_cv=0;
               
               -- set calculation period
               startdate_of_month := from_date::DATE;
               enddate_of_month := to_date::DATE;
               
               -- clear the previous history of ranks for the selected month
              delete from user_rank_history where "period" = enddate_of_month;
              delete from rank_history where created_dt >= startdate_of_month and created_dt <= enddate_of_month + interval \'1 day - 1 millisecond\';
			   
               
               -- Open the cursor cur_transacs
               
               --update all users PQV for the running month through and 20190528 fix pqv range from 1 month to -30 days
               ----update users set current_month_pqv=su.qv
               ----from (SELECT userid,sum(qv) as qv FROM vorder_product_qv 
               ----      where date(created_dt) between date_trunc(\'day\', NOW())::DATE - interval \'30 days\' and date_trunc(\'day\', NOW())::DATE + interval \' + 1 day - 1 second\' group by userid) su
               ----where users.id=su.userid;
               
               --TODO
               --accumulate all users\' customers PQV of respective users along with the users PQV
               
               
             OPEN cur_transacs(startdate_of_month,enddate_of_month);    
               LOOP
                -- fetch row into the transac
                FETCH cur_transacs INTO transac;		
            
                -- exit when no more row to fetch
                EXIT WHEN NOT FOUND;	
            
                --a counter representing the upper level the sponsor
                counter:=1;				
            
                --go up the sponsor tree and add an QV entry to each user on the upline
            
                --pick the sponsor id of the user
                SELECT sponsorid into sp_id from public."users" where id=transac.userid;
                
                SELECT count(id) into is_user_enabled from users where id = transac.userid and account_status not in (\'TERMINATED\', \'SUSPENDED\');
                
                IF is_user_enabled > 0 then
            		INSERT INTO qv_transaction (transaction_id,transaction_date, qv, user_id, level,initiated_user_id, cv)
                    VALUES (transac.orderid,transac.created_dt, transac.qv, transac.userid, counter,transac.userid, transac.cv);	
                END IF; 
                    
                LOOP 
                    -- get the sponsor of the buyer, sponsor\'s sponsor and so on	
                    SELECT * FROM users INTO sponsor WHERE distid=sp_id;
                    -- exit the loop if there no longer a sponsor of the buyer or the sponsor
                    EXIT WHEN NOT FOUND; --or sponsor.id is null;	
                    
                    IF sponsor.account_status not in (\'TERMINATED\', \'SUSPENDED\') then
	                   INSERT INTO qv_transaction (transaction_id,transaction_date, qv, user_id, level,initiated_user_id, cv)
                       VALUES (transac.orderid,transac.created_dt, transac.qv, sponsor.id, counter,transac.userid, transac.cv);
                    END IF;
							
                    sp_id:=sponsor.sponsorid;
                    
                    --add one to the level to which we are going to add  QV next
                    counter := counter + 1 ; 
                        
                END LOOP ; 					
                         
                
               END LOOP;
               -- Close the cursor cur_transacs
               CLOSE cur_transacs; 
               
               
              
               -- Update the current month qv of each user in the user table  
               UPDATE users SET current_month_qv=sq.totalqv, current_month_cv=sq.totalcv
               FROM (select user_id,sum(qv) as totalqv, sum(cv) as totalcv from qv_transaction group by user_id) as sq
               WHERE sq.user_id=users.id;
               
                  -- Open the cursor all_users
               OPEN cur_all_users;       
               LOOP
                    -- fetch row into the cur_user
                    FETCH cur_all_users INTO cuser;
                    -- exit when no more row to fetch
                    EXIT WHEN NOT FOUND;	
                    
                    --getcount of active TSA
                    SELECT count(1) into total_tsa FROM enrolment_tree_tsa(cuser.distid) where is_active=1;
                        
                    --Update current month TSA count of each user
                    UPDATE users SET current_month_tsa=total_tsa where id=cuser.id;  
                
               END LOOP;
               -- Close the cursor all_users
               CLOSE cur_all_users;  
               
               -- Open the cursor all_users
               OPEN cur_all_users;       
               LOOP
                    -- fetch row into the cur_user
                    FETCH cur_all_users INTO cuser;
                    -- exit when no more row to fetch
                    EXIT WHEN NOT FOUND;
					
					SELECT bp.id INTO binary_id FROM users u
					JOIN binary_plan bp
					ON bp.user_id = u.id
					WHERE u.distid = cuser.distid;
            
                    OPEN cur_rank_defs;
                    LOOP
                        FETCH cur_rank_defs INTO rdef;			
                        --exit when no more row to fetch
                        EXIT WHEN NOT FOUND;	
						
						IF rdef.rankval >= 50 THEN
							-- get left and right active users
							SELECT * FROM get_user_subtree(\'L\', binary_id) INTO tbl_left;
							SELECT * FROM get_user_subtree(\'R\', binary_id) INTO tbl_right;

							SELECT check_binary_verification(tbl_left.left_key, tbl_left.right_key) INTO active_left;
							SELECT check_binary_verification(tbl_right.left_key, tbl_right.right_key) INTO active_right;						
						ELSE
							active_left = 5;
							active_right = 5;
						END IF;
                
                        SELECT * FROM get_rank_by_percentage(cuser.distid,rdef.id,rdef.rank_limit,rdef.min_qv) INTO crank;
						rank_remarks:=\'QV\';	
                        
                        --RAISE NOTICE \'Rank Value calculated %\',crank.rankval;			
                        IF crank.rankval IS NOT NULL THEN
                        
                            SELECT count(*) FROM rank_history INTO rank_exists 
                                WHERE users_id=cuser.id 
                                AND lifetime_rank=crank.rankval
                                AND created_dt >= startdate_of_month 
                                AND created_dt <= enddate_of_month + interval \'1 day - 1 millisecond\';
                            IF rank_exists<1 THEN
                                INSERT INTO rank_history(users_id,lifetime_rank,created_dt,remarks)  values(cuser.id,crank.rankval,enddate_of_month,rank_remarks);
                            END IF;
            
                            --take the total qv from users
                            select current_month_qv, current_month_cv into curr_month_qv, curr_month_cv from users where id=cuser.id;
                            
                            --Checking whether data already inserted to the user_rank_history table. 
                            select count(*) from user_rank_history into rank_exists where period=enddate_of_month and user_id=cuser.id;		
                                
							curr_month_tsa := cuser.current_month_tsa;
							if cuser.is_active=1 then
								curr_month_tsa := curr_month_tsa - 1;
							end if;
							
                            if rank_exists>0 then 
                            --Data already available for a particular user for particular month, so update				
                                UPDATE public.user_rank_history 
                                SET monthly_rank=crank.rankval,monthly_rank_desc=crank.rankdesc,monthly_qv= curr_month_qv,qualified_qv=crank.rank_qv,qualified_tsa=0,monthly_tsa=curr_month_tsa,monthly_cv = curr_month_cv
                                WHERE user_id=cuser.id and period=enddate_of_month;		
                            else
                            --Data unavailable for a particular user for particular month, so insert
                                INSERT INTO public.user_rank_history(user_id, monthly_rank,monthly_rank_desc, period, monthly_qv,qualified_qv,qualified_tsa,monthly_tsa, monthly_cv) 
                                VALUES (cuser.id, crank.rankval,crank.rankdesc,enddate_of_month, curr_month_qv,crank.rank_qv,0,curr_month_tsa, curr_month_cv);
                            end if;	
            
                            --Updates the user table current rank 
                            update users set current_month_rank=crank.rankval where id=cuser.id;
                        ELSE
							EXIT;
                        END IF;
                    END LOOP;
                    CLOSE cur_rank_defs;		
                            
                    
                    SELECT count(*) FROM rank_history INTO rank_exists WHERE users_id=cuser.id;
                    IF rank_exists<1 THEN
                        INSERT INTO rank_history(users_id,lifetime_rank,created_dt)  values(cuser.id,10,enddate_of_month);
                    END IF;
                    
               END LOOP;
               -- Close the cursor all_users
               CLOSE cur_all_users;      
               raise info \'Ended %\',now();
               
               --insert a record that this process ran on certain date and time
               truncate rank_log;
               insert into rank_log (worked_on) values(now());
                    
            END;$BODY$;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('
CREATE OR REPLACE FUNCTION public.calculate_downline_qv_with_tsa(
	)
    RETURNS void
    LANGUAGE \'plpgsql\'

    COST 100
    VOLATILE 
AS $BODY$DECLARE 
             startdate_of_month timestamp;
             enddate_of_month timestamp;
             transac   RECORD;
             sponsor RECORD;
             cuser RECORD;
             rdef RECORD;
             sp_id varchar(100);
             counter integer :=0;
             rank_qv integer :=0;
             total_tsa integer :=0;
             rank_desc varchar(100);
             crank rank;
             crank2 tsarank;
             --tsarank type to get the tsa count and rankval
             trank tsarank;
             rank_exists integer :=0;
             curr_month_qv bigint:=0;
             curr_month_pqv bigint:=0;
			 curr_month_tsa integer:=0;
			 curr_month_cv bigint:=0;
             qualify_qv bigint:=0;
             is_user_enabled integer :=0;
             
                --This variable is to sent inside rank history table Remarks field flaging how a user was promoted 
               --either by QV or TSA Count or False Promoted
             rank_remarks varchar(100);
             
             cur_transacs CURSOR (d1 date,d2 date)
                FOR SELECT * FROM vorder_product_qv where created_dt >= d1 and created_dt <= d2 + interval \'1 day - 1 millisecond\';
            
             cur_all_users CURSOR
                FOR SELECT id,distid,COALESCE(current_month_qv, 0) as current_month_qv,COALESCE(current_month_tsa, 0) as current_month_tsa,COALESCE(current_month_pqv, 0) as current_month_pqv, COALESCE(current_month_cv, 0) as current_month_cv,is_active FROM users where account_status not in (\'TERMINATED\', \'SUSPENDED\');
                
             cur_rank_defs CURSOR
                FOR SELECT * FROM rank_definition ORDER BY rankval;
                 
             BEGIN
            
               
               TRUNCATE table "qv_transaction";
               --20190424 modified to update pqv=0 as every month during every time it is reset to 0
               update users set current_month_pqv=0,current_month_tsa=0,current_month_qv=0,current_month_rank=10,current_month_cv=0;
               
               -- first date of month
               SELECT date_trunc(\'month\', NOW())::DATE into startdate_of_month;
			   
               
               -- last date of month
               SELECT (date_trunc(\'month\', NOW())::DATE + interval \'1 month - 1 day\')::DATE into enddate_of_month;
			   
               
               -- Open the cursor cur_transacs
               
               --update all users PQV for the running month through and 20190528 fix pqv range from 1 month to -30 days
               ----update users set current_month_pqv=su.qv
               ----from (SELECT userid,sum(qv) as qv FROM vorder_product_qv 
               ----      where date(created_dt) between date_trunc(\'day\', NOW())::DATE - interval \'30 days\' and date_trunc(\'day\', NOW())::DATE + interval \' + 1 day - 1 second\' group by userid) su
               ----where users.id=su.userid;
               
               --TODO
               --accumulate all users\' customers PQV of respective users along with the users PQV
               
               
             OPEN cur_transacs(startdate_of_month,enddate_of_month);    
               LOOP
                -- fetch row into the transac
                FETCH cur_transacs INTO transac;		
            
                -- exit when no more row to fetch
                EXIT WHEN NOT FOUND;	
            
                --a counter representing the upper level the sponsor
                counter:=1;				
            
                --go up the sponsor tree and add an QV entry to each user on the upline
            
                --pick the sponsor id of the user
                SELECT sponsorid into sp_id from public."users" where id=transac.userid;
                
                SELECT count(id) into is_user_enabled from users where id = transac.userid and account_status not in (\'TERMINATED\', \'SUSPENDED\');
                
                IF is_user_enabled > 0 then
            		INSERT INTO qv_transaction (transaction_id,transaction_date, qv, user_id, level,initiated_user_id, cv)
                    VALUES (transac.orderid,transac.created_dt, transac.qv, transac.userid, counter,transac.userid, transac.cv);	
                END IF;                    
                    
                LOOP 
                    -- get the sponsor of the buyer, sponsor\'s sponsor and so on	
                    SELECT * FROM users INTO sponsor WHERE distid=sp_id;
                    -- exit the loop if there no longer a sponsor of the buyer or the sponsor
                    EXIT WHEN NOT FOUND; --or sponsor.id is null;	
                    
                    IF sponsor.account_status not in (\'TERMINATED\', \'SUSPENDED\') then
	                   INSERT INTO qv_transaction (transaction_id,transaction_date, qv, user_id, level,initiated_user_id, cv)
                       VALUES (transac.orderid,transac.created_dt, transac.qv, sponsor.id, counter,transac.userid, transac.cv);
                    END IF;
							
                    sp_id:=sponsor.sponsorid;
                    --add one to the level to which we are going to add  QV next
                    counter := counter + 1 ; 
                        
                END LOOP ; 					
                         
                
               END LOOP;
               -- Close the cursor cur_transacs
               CLOSE cur_transacs; 
               
               
              
               -- Update the current month qv of each user in the user table  
               UPDATE users SET current_month_qv=sq.totalqv, current_month_cv=sq.totalcv
               FROM (select user_id,sum(qv) as totalqv, sum(cv) as totalcv from qv_transaction group by user_id) as sq
               WHERE sq.user_id=users.id;
               
                  -- Open the cursor all_users
               OPEN cur_all_users;       
               LOOP
                    -- fetch row into the cur_user
                    FETCH cur_all_users INTO cuser;
                    -- exit when no more row to fetch
                    EXIT WHEN NOT FOUND;	
                    
                    --getcount of active TSA
                    SELECT count(1) into total_tsa FROM enrolment_tree_tsa(cuser.distid) where is_active=1;
                        
                    --Update current month TSA count of each user
                    UPDATE users SET current_month_tsa=total_tsa where id=cuser.id;  
                
               END LOOP;
               -- Close the cursor all_users
               CLOSE cur_all_users;  
               
               -- Open the cursor all_users
               OPEN cur_all_users;       
               LOOP
                    -- fetch row into the cur_user
                    FETCH cur_all_users INTO cuser;
                    -- exit when no more row to fetch
                    EXIT WHEN NOT FOUND;	
            
                    OPEN cur_rank_defs;
                    LOOP
                        FETCH cur_rank_defs INTO rdef;			
                        --exit when no more row to fetch
                        EXIT WHEN NOT FOUND;	   					
                
                        SELECT * FROM get_rank_by_percentage(cuser.distid,rdef.id,rdef.rank_limit,rdef.min_qv) INTO crank;
                        
                        --SELECT * FROM get_active_tsa_count(cuser.distid,rdef.id,rdef.rank_limit,rdef.min_tsa) INTO crank2;
                        
                        rank_remarks:=\'QV\';	
                        
                        --SELECT count(1) into total_tsa FROM enrolment_tree_tsa(cuser.distid) where current_month_pqv>=100 and distid!=cuser.distid;
                        
                        --RAISE NOTICE \'Rank Value calculated %\',crank.rankval;			
                        IF crank.rankval IS NOT NULL THEN
                        
                            SELECT count(*) FROM rank_history INTO rank_exists 
                                WHERE users_id=cuser.id 
                                AND lifetime_rank=crank.rankval
                                AND created_dt >= startdate_of_month 
	                        	AND created_dt <= enddate_of_month + interval \'1 day - 1 millisecond\';                                
                            IF rank_exists<1 THEN
                                INSERT INTO rank_history(users_id,lifetime_rank,created_dt,remarks)  values(cuser.id,crank.rankval,now(),rank_remarks);
                            END IF;
            
                            --take the total qv from users
                            select current_month_qv, current_month_cv into curr_month_qv, curr_month_cv from users where id=cuser.id;
                            
                            --Checking whether data already inserted to the user_rank_history table. 
                            select count(*) from user_rank_history into rank_exists where period=enddate_of_month and user_id=cuser.id;		
                                
							curr_month_tsa := cuser.current_month_tsa;
							if cuser.is_active=1 then
								curr_month_tsa := curr_month_tsa - 1;
							end if;
							
                            if rank_exists>0 then 
                            --Data already available for a particular user for particular month, so update				
                                UPDATE public.user_rank_history 
                                SET monthly_rank=crank.rankval,monthly_rank_desc=crank.rankdesc,monthly_qv= curr_month_qv,qualified_qv=crank.rank_qv,qualified_tsa=0,monthly_tsa=curr_month_tsa,monthly_cv = curr_month_cv
                                WHERE user_id=cuser.id and period=enddate_of_month;		
                            else
                            --Data unavailable for a particular user for particular month, so insert
                                INSERT INTO public.user_rank_history(user_id, monthly_rank,monthly_rank_desc, period, monthly_qv,qualified_qv,qualified_tsa,monthly_tsa, monthly_cv) 
                                VALUES (cuser.id, crank.rankval,crank.rankdesc,enddate_of_month, curr_month_qv,crank.rank_qv,0,curr_month_tsa, curr_month_cv);
                            end if;	
            
                            --Updates the user table current rank 
                            update users set current_month_rank=crank.rankval where id=cuser.id;
                        END IF;
                        
                    END LOOP;
                    CLOSE cur_rank_defs;		
                            
                    
                    SELECT count(*) FROM rank_history INTO rank_exists WHERE users_id=cuser.id;
                    IF rank_exists<1 THEN
                        INSERT INTO rank_history(users_id,lifetime_rank,created_dt)  values(cuser.id,10,now());
                    END IF;
                    
               END LOOP;
               -- Close the cursor all_users
               CLOSE cur_all_users;      
               raise info \'Ended %\',now();
               
               --insert a record that this process ran on certain date and time
               truncate rank_log;
               insert into rank_log (worked_on) values(now());
                    
            END;$BODY$;
        ');

        DB::statement('
 CREATE OR REPLACE FUNCTION public.get_rank_by_percentage(
	dist_id character varying,
	rankdefid bigint,
	ranklimit numeric,
	qualifyqv bigint)
    RETURNS rank
    LANGUAGE \'plpgsql\'

    COST 100
    VOLATILE 
AS $BODY$DECLARE 
 	cur_rank rank;
 	rank_qv bigint;
 BEGIN
    --RAISE NOTICE \'QUALIFY QV %\',qualifyqv;
	--RAISE NOTICE \'RANK LIMIT %\',ranklimit;
	
	SELECT sum(
		CASE WHEN COALESCE(current_month_qv, 0) > qualifyqv*ranklimit 
			THEN qualifyqv*ranklimit 
		ELSE COALESCE(current_month_qv, 0) 
		END) INTO rank_qv
	FROM users WHERE sponsorid = dist_id;
	
	--RAISE NOTICE \'RANK QV %\',rank_qv;
	
	SELECT rankval,rankdesc,rank_qv 
	FROM rank_definition INTO cur_rank  
	WHERE
		rank_qv >= min_qv
		AND id = rankdefid
		LIMIT 1;
	
	--RAISE NOTICE \'RANK VALUE %\',cur_rank.rankval;
	
	RETURN cur_rank;
		
END;$BODY$;  
        ');

        DB::statement('DROP FUNCTION IF EXISTS check_binary_verification(integer, integer);');

        DB::statement('
CREATE OR REPLACE FUNCTION public.calculate_month_downline_qv(
	from_date timestamp without time zone,
	to_date timestamp without time zone)
    RETURNS void
    LANGUAGE \'plpgsql\'

    COST 100
    VOLATILE 
AS $BODY$DECLARE 
             startdate_of_month timestamp;
             enddate_of_month timestamp;
             transac   RECORD;
             sponsor RECORD;
             cuser RECORD;
             rdef RECORD;
             sp_id varchar(100);
             counter integer :=0;
             rank_qv integer :=0;
             total_tsa integer :=0;
             rank_desc varchar(100);
             crank rank;
             crank2 tsarank;
             --tsarank type to get the tsa count and rankval
             trank tsarank;
             rank_exists integer :=0;
             curr_month_qv bigint:=0;
             curr_month_pqv bigint:=0;
			 curr_month_tsa integer:=0;
			 curr_month_cv bigint:=0;
             qualify_qv bigint:=0;
             is_user_enabled integer :=0;
             
                --This variable is to sent inside rank history table Remarks field flaging how a user was promoted 
               --either by QV or TSA Count or False Promoted
             rank_remarks varchar(100);
             
             cur_transacs CURSOR (d1 date,d2 date)
                FOR SELECT * FROM vorder_product_qv where created_dt >= d1 and created_dt <= d2 + interval \'1 day - 1 millisecond\';
            
             cur_all_users CURSOR
                FOR SELECT id,distid,COALESCE(current_month_qv, 0) as current_month_qv,COALESCE(current_month_tsa, 0) as current_month_tsa,COALESCE(current_month_pqv, 0) as current_month_pqv, COALESCE(current_month_cv, 0) as current_month_cv,is_active FROM users where account_status not in (\'TERMINATED\', \'SUSPENDED\');
                
             cur_rank_defs CURSOR
                FOR SELECT * FROM rank_definition ORDER BY rankval;
                 
             BEGIN
            
               
               TRUNCATE table "qv_transaction";
               --20190424 modified to update pqv=0 as every month during every time it is reset to 0
               update users set current_month_pqv=0,current_month_tsa=0,current_month_qv=0,current_month_rank=10,current_month_cv=0;
               
               -- set calculation period
               startdate_of_month := from_date::DATE;
               enddate_of_month := to_date::DATE;
               
               -- clear the previous history of ranks for the selected month
              delete from user_rank_history where "period" = enddate_of_month;
              delete from rank_history where created_dt >= startdate_of_month and created_dt <= enddate_of_month + interval \'1 day - 1 millisecond\';
			   
               
               -- Open the cursor cur_transacs
               
               --update all users PQV for the running month through and 20190528 fix pqv range from 1 month to -30 days
               ----update users set current_month_pqv=su.qv
               ----from (SELECT userid,sum(qv) as qv FROM vorder_product_qv 
               ----      where date(created_dt) between date_trunc(\'day\', NOW())::DATE - interval \'30 days\' and date_trunc(\'day\', NOW())::DATE + interval \' + 1 day - 1 second\' group by userid) su
               ----where users.id=su.userid;
               
               --TODO
               --accumulate all users\' customers PQV of respective users along with the users PQV
               
               
             OPEN cur_transacs(startdate_of_month,enddate_of_month);    
               LOOP
                -- fetch row into the transac
                FETCH cur_transacs INTO transac;		
            
                -- exit when no more row to fetch
                EXIT WHEN NOT FOUND;	
            
                --a counter representing the upper level the sponsor
                counter:=1;				
            
                --go up the sponsor tree and add an QV entry to each user on the upline
            
                --pick the sponsor id of the user
                SELECT sponsorid into sp_id from public."users" where id=transac.userid;
                
                SELECT count(id) into is_user_enabled from users where id = transac.userid and account_status not in (\'TERMINATED\', \'SUSPENDED\');
                
                IF is_user_enabled > 0 then
            		INSERT INTO qv_transaction (transaction_id,transaction_date, qv, user_id, level,initiated_user_id, cv)
                    VALUES (transac.orderid,transac.created_dt, transac.qv, transac.userid, counter,transac.userid, transac.cv);	
                END IF; 
                    
                LOOP 
                    -- get the sponsor of the buyer, sponsor\'s sponsor and so on	
                    SELECT * FROM users INTO sponsor WHERE distid=sp_id;
                    -- exit the loop if there no longer a sponsor of the buyer or the sponsor
                    EXIT WHEN NOT FOUND; --or sponsor.id is null;	
                    
                    IF sponsor.account_status not in (\'TERMINATED\', \'SUSPENDED\') then
	                   INSERT INTO qv_transaction (transaction_id,transaction_date, qv, user_id, level,initiated_user_id, cv)
                       VALUES (transac.orderid,transac.created_dt, transac.qv, sponsor.id, counter,transac.userid, transac.cv);
                    END IF;
							
                    sp_id:=sponsor.sponsorid;
                    
                    --add one to the level to which we are going to add  QV next
                    counter := counter + 1 ; 
                        
                END LOOP ; 					
                         
                
               END LOOP;
               -- Close the cursor cur_transacs
               CLOSE cur_transacs; 
               
               
              
               -- Update the current month qv of each user in the user table  
               UPDATE users SET current_month_qv=sq.totalqv, current_month_cv=sq.totalcv
               FROM (select user_id,sum(qv) as totalqv, sum(cv) as totalcv from qv_transaction group by user_id) as sq
               WHERE sq.user_id=users.id;
               
                  -- Open the cursor all_users
               OPEN cur_all_users;       
               LOOP
                    -- fetch row into the cur_user
                    FETCH cur_all_users INTO cuser;
                    -- exit when no more row to fetch
                    EXIT WHEN NOT FOUND;	
                    
                    --getcount of active TSA
                    SELECT count(1) into total_tsa FROM enrolment_tree_tsa(cuser.distid) where is_active=1;
                        
                    --Update current month TSA count of each user
                    UPDATE users SET current_month_tsa=total_tsa where id=cuser.id;  
                
               END LOOP;
               -- Close the cursor all_users
               CLOSE cur_all_users;  
               
               -- Open the cursor all_users
               OPEN cur_all_users;       
               LOOP
                    -- fetch row into the cur_user
                    FETCH cur_all_users INTO cuser;
                    -- exit when no more row to fetch
                    EXIT WHEN NOT FOUND;	
            
                    OPEN cur_rank_defs;
                    LOOP
                        FETCH cur_rank_defs INTO rdef;			
                        --exit when no more row to fetch
                        EXIT WHEN NOT FOUND;	   					
                
                        SELECT * FROM get_rank_by_percentage(cuser.distid,rdef.id,rdef.rank_limit,rdef.min_qv) INTO crank;
                        
                        --SELECT * FROM get_active_tsa_count(cuser.distid,rdef.id,rdef.rank_limit,rdef.min_tsa) INTO crank2;
                        
                        rank_remarks:=\'QV\';	
                        
                        --SELECT count(1) into total_tsa FROM enrolment_tree_tsa(cuser.distid) where current_month_pqv>=100 and distid!=cuser.distid;
                        
                        --RAISE NOTICE \'Rank Value calculated %\',crank.rankval;			
                        IF crank.rankval IS NOT NULL THEN
                        
                            SELECT count(*) FROM rank_history INTO rank_exists 
                                WHERE users_id=cuser.id 
                                AND lifetime_rank=crank.rankval
                                AND created_dt >= startdate_of_month 
                                AND created_dt <= enddate_of_month + interval \'1 day - 1 millisecond\';
                            IF rank_exists<1 THEN
                                INSERT INTO rank_history(users_id,lifetime_rank,created_dt,remarks)  values(cuser.id,crank.rankval,enddate_of_month,rank_remarks);
                            END IF;
            
                            --take the total qv from users
                            select current_month_qv, current_month_cv into curr_month_qv, curr_month_cv from users where id=cuser.id;
                            
                            --Checking whether data already inserted to the user_rank_history table. 
                            select count(*) from user_rank_history into rank_exists where period=enddate_of_month and user_id=cuser.id;		
                                
							curr_month_tsa := cuser.current_month_tsa;
							if cuser.is_active=1 then
								curr_month_tsa := curr_month_tsa - 1;
							end if;
							
                            if rank_exists>0 then 
                            --Data already available for a particular user for particular month, so update				
                                UPDATE public.user_rank_history 
                                SET monthly_rank=crank.rankval,monthly_rank_desc=crank.rankdesc,monthly_qv= curr_month_qv,qualified_qv=crank.rank_qv,qualified_tsa=0,monthly_tsa=curr_month_tsa,monthly_cv = curr_month_cv
                                WHERE user_id=cuser.id and period=enddate_of_month;		
                            else
                            --Data unavailable for a particular user for particular month, so insert
                                INSERT INTO public.user_rank_history(user_id, monthly_rank,monthly_rank_desc, period, monthly_qv,qualified_qv,qualified_tsa,monthly_tsa, monthly_cv) 
                                VALUES (cuser.id, crank.rankval,crank.rankdesc,enddate_of_month, curr_month_qv,crank.rank_qv,0,curr_month_tsa, curr_month_cv);
                            end if;	
            
                            --Updates the user table current rank 
                            update users set current_month_rank=crank.rankval where id=cuser.id;
                        END IF;
                        
                    END LOOP;
                    CLOSE cur_rank_defs;		
                            
                    
                    SELECT count(*) FROM rank_history INTO rank_exists WHERE users_id=cuser.id;
                    IF rank_exists<1 THEN
                        INSERT INTO rank_history(users_id,lifetime_rank,created_dt)  values(cuser.id,10,enddate_of_month);
                    END IF;
                    
               END LOOP;
               -- Close the cursor all_users
               CLOSE cur_all_users;      
               raise info \'Ended %\',now();
               
               --insert a record that this process ran on certain date and time
               truncate rank_log;
               insert into rank_log (worked_on) values(now());
                    
            END;$BODY$;
        ');
    }
}
