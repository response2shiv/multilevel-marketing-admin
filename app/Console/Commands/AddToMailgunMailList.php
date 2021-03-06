<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddToMailgunMailList extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:add_to_mailgun_mail_list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        if (env('APP_ENV') === 'prod') {
            \App\MailGunMailList::addBulkUsers();
        }
    }

}
