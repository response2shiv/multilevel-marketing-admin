@include('affiliate.layouts.new_modal_style')
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-body dlgQuestionList">
            <div style="padding:10px 0px;">
                <img src="{{asset('/assets/images/xccelerate.png')}}" width="180px;">
            </div>
            <div class="cm-body" style="background: #4aafd1">
                <div class="cm-body-inner">
                    <h3>Cart</h3>
                    <form id="frmCheckOutPaymentEventsTicket">
                        @csrf
                        <input type="hidden" name="typeOfPurchase" id="typeOfPurchase" value="events-ticket">

                        <div class="order-detail-wrap">

                            <div class="order-details">
                                <h4>order details</h4>
                                <table class="product-tbl" id="ticket_product_table">
                                    <tr class="prdct-head">
                                        <td>PRODUCT</td>
                                        <td>PRICE</td>
                                        <td>QUANTITY</td>
                                    </tr>
                                    @foreach($products as $item)

                                    <input type="hidden" name="quantity[{{ $item->product->id }}]" value="{{ $item->quantity }}">

                                    <tr class="prdct-item">


                                        <td class="prdct-name">

                                            <p>{{$item->product->productname}}</p>
                                        </td>

                                        <td>
                                            <strong>${{number_format($item->product->price,2)}}</strong>
                                        </td>
                                        <td>
                                            <strong>{{  $item->quantity }}</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                    <tr class="total-price" style="font-weight: bold;">
                                        <td> TOTAL</td>
                                        <td> {{number_format($total, 2)}}</td>
                                    </tr>
                                </table>

                                <div class="coupon-code" style="margin-top: 20px">
                                        <div class="copuon-lt">
                                            <label> Do you have a coupon code?</label>
                                            <input type="text" name="coupon" id="coupon" class="input-box">
                                        </div>
                                    <div class="coupn-btn">
                                        <input type="button" id="btnApplyCheckOutTicketCoupon" class="yellow-btn"
                                               value="Apply">
                                    </div>
                                </div>

                            </div>
                            <div class="quick-checkout">
                                <h4>quick checkout</h4>
                                <div class="payment-method">
                                    <div class="payment-field">
                                        <form id="frmCheckOutPayment">
                                            <ul class="list-group">
                                                <label>payment method <span class="req">*</span></label>
                                                <li class="list-group-item payment-method-options-active text-left">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" id="new_card" name="payment_method" value="new_card">
                                                        <label class="form-check-label" for="new_card">ADD NEW CARD</label>
                                                    </div>
                                                </li>
                                                <li class="list-group-item payment-method-options-active text-left">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" id="e-wallet" name="payment_method" value="e_wallet">
                                                        <label class="form-check-label" for="e-wallet">E-WALLET</label>
                                                    </div>
                                                </li>

                                                <?php
                                                if (!empty($cvv)) {
                                                    foreach ($cvv as $key => $cv) {
                                                        if(!empty($cv->token)){
                                                            if ($cv->is_restricted) {
                                                                continue;
                                                            }
                                                            
                                                            if ($cv->is_deleted) {
                                                                $elem = '<li class="list-group-item payment-method-options-inactive text-left" id="payment-method-options-inactive">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio" id="card-'. $key .'" name="payment_method" value="'. $cv->id .'" disabled>
                                                                            <label class="form-check-label" for="card-'. $key .'">CARD ENDING IN ' . substr($cv->token, -4) . '</label>
                                                                        </div>
                                                                        <div class="pl-4" id="status-inactive">Status: Inactive</div>
                                                                        <div>
                                                                            <button class="btn btn-warning btn-sm btn-block mt-1 text-white" id="btn-payment-methods">Activate</button>
                                                                        </div>
                                                                        </li>
                                                                ';
                                                            } else {
                                                                $elem = '<li class="list-group-item payment-method-options-active text-left" id="payment-method-options-active">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input" type="radio" id="card-'. $key .'" name="payment_method" value="'. $cv->id .'">
                                                                            <label class="form-check-label" for="card-'. $key .'">CARD ENDING IN ' . substr($cv->token, -4) . '</label>
                                                                        </div>
                                                                        <div class="pl-4">Status: Active</div>
                                                                        </li>
                                                                ';
                                                            }

                                                            echo $elem;
                                                        }
                                                    }
                                                }
                                                ?>
                                            </ul>
                                            <input type="hidden" name="discount_coupon">
                                        </form>
                                    </div>
                                </div>
                            </div>


                        <div class="submit-cart submit-btn-grp" style="margin-top:30px">
                            <button data-id="1" class="blue-btn" id="btnBackCheckOutPaymentTicketPacks">BACK</button>
                            <button class="yellow-btn" id="btnConfirmCheckOutPaymentTicketPacks">SUBMIT</button>
                        </div>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $( document ).ready(function() {
        $('#frmCheckOutPaymentEventsTicket').submit(function (e) {
            e.preventDefault()  // prevent the form from 'submitting'
        })
    });

    $( document ).ready(function() {
        $( "#coupon" ).focus(function() {
            this.value = '';
        });
    });

</script>
