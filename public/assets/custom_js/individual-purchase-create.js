loadDatePicker()

// =============================================
// BARCODE SCANNER
// =============================================
function processBarcodeScan(barcode) {
    let selectedOption = $('.select-product option').filter(function () {
        return $(this).attr('data-product-code') === barcode || $(this).attr('data-barcode') === barcode;
    });

    if (selectedOption.length === 0) {
        return false;
    }

    let productId = selectedOption.val();
    let hasSerial = selectedOption.data('has-serial');
    
    // Check if the product is already in the table
    let existingRow = $('.product-display tr').find('.product-id[value="' + productId + '"]').closest('tr');
    
    if (existingRow.length > 0) {
        if (hasSerial == 1) {
            showSerialProductPopup(productId);
            $('#add_product_serial_modal').modal('show');
        } else {
            let qtyInput = existingRow.find('.product-quantity');
            let currentQty = parseFloat(qtyInput.val()) || 0;
            qtyInput.val(currentQty + 1);
            changeQuantity(qtyInput[0]); 
        }
        
        $('.select-quantity').val('');
        $('.select-product').val('').trigger('change.select2');
        setTimeout(function() {
            $('#barcode_input').val('').focus();
        }, 100);
        
        return true;
    }

    $('.select-product').val(productId).trigger('change');
    
    if (hasSerial != 1) {
        $('.select-quantity').val(1);
    }
    
    insertNewItem();
    
    if (hasSerial == 1) {
        showSerialProductPopup(productId);
        $('#add_product_serial_modal').modal('show');
    } else {
        setTimeout(function() {
            $('#barcode_input').val('').focus();
        }, 100);
    }
    
    return true;
}

$(document).on('keypress', '#barcode_input', function (event) {
    if (event.key !== 'Enter') return;
    event.preventDefault();

    let barcode = $(this).val().trim();
    if (barcode === '') return;

    let success = processBarcodeScan(barcode);
    if (!success) {
        alert('Product not found: ' + barcode);
    }
    
    $(this).val('');
});

// F2 and F3 Shortcuts
$(document).on('keydown', function (event) {
    if (event.key === 'F2') {
        event.preventDefault();
        $('#barcode_input').focus();
    }
    if (event.key === 'F3') {
        event.preventDefault();
        $('.select-product').select2('open');
    }
});


$('.select-product').change(function () {
    let productCost = $(this).find('option:selected').data('product-cost')
    let hasSerial = $(this).find('option:selected').data('has-serial')


    $('.select-unit-cost').val(productCost)
    $('.select-product').select2();

    if (hasSerial == 1) {
        $('.select-quantity').val(0)
        $('.select-quantity').prop('readonly', true);
        $('.select-unit-cost').focus()
    } else {
        $('.select-quantity').prop('readonly', false);
        $('.select-quantity').focus()
    }

})

$('.select-quantity').keypress(function(event) {
    let keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13') {
        let val = $(this).val().trim();
        if (val.length > 3) {
            // Check if it's a barcode scan that landed here
            let isBarcode = $('.select-product option').filter(function () {
                return $(this).attr('data-product-code') === val || $(this).attr('data-barcode') === val;
            }).length > 0;
            
            if (isBarcode) {
                $(this).val(1); // Reset to 1 as it was likely overwritten by the scan
                processBarcodeScan(val);
                return false;
            }
        }
        
        $('.select-free-quantity').val(0)
        $('.select-unit-cost').focus()
        return false;
    }
});

$('.select-unit-cost').keypress(function(event) {
    let keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13') {
        let val = $(this).val().trim();
        if (val.length > 3) {
            let selectedOption = $('.select-product option').filter(function () {
                return $(this).attr('data-product-code') === val || $(this).attr('data-barcode') === val;
            });
            
            if (selectedOption.length > 0) {
                // Restore unit cost
                $(this).val(selectedOption.data('product-cost'));
                processBarcodeScan(val);
                return false;
            }
        }
        
        $('.select-free-quantity').focus()
    }
});

// $('.select-free-quantity').keypress(function(event) {
//     let keycode = (event.keyCode ? event.keyCode : event.which);
//     if(keycode == '13') {
//         return false;
//     }
// });

$('.select-free-quantity').keyup(function(event) {
    let keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13') {
        insertNewItem()
    }
});

// '<td><input type="text" name="expire_dates[' + productId + ']" autocomplete="off" placeholder="dd/mm/yyyy" value="" class="form-control expire-date">';
function insertNewItem () {
    let selected_item   = $('.select-product').find('option:selected');
    let productId       = selected_item.val()
    let hasSerial       = selected_item.data('has-serial')
    let productName     = selected_item.data('product-name')
    let productCode     = selected_item.data('product-code')
    let productCost     = $('.select-unit-cost').val()
    let quantity        = $('.select-quantity').val()

    let freeQuantity    = Number($('.select-free-quantity').val() | 0)

    if (quantity === '' && freeQuantity > 0) {
        quantity = 0;
    }

    if (productId != '' && quantity !== '') {
            let tr =    '<tr>' +
                '<td class="item-serial"></td>' +
                '<td>' + productName + '<input type="hidden" name="product_ids[' + productId + ']" class="product-id" value="' + productId + '"></td>' +
                '<td>' + productCode + '</td>';
                if (hasSerial == 1) {
                    tr += '<td>'
                        tr += '<div class="input-group">' +
                                '<input type="text" id="serial_qty_input_' + productId + '" readonly  name="quantities[' + productId + ']" class="product-quantity form-control text-center" value="' + quantity + '">' +

                                '<div class="input-group-append">' +
                                    '<span style="cursor: pointer; padding-top: 5px" data-toggle="modal" data-target="#add_product_serial_modal" onclick="showSerialProductPopup(' + productId + ')" class="btn btn-sm btn-dark" title="Add Serial">+</span>' +
                                '</div>' +
                            '</div>' +
                            '<textarea style="display:none" name="serials[' + productId + ']" id="serial_qty_textarea_' + productId + '"></textarea>' +
                        '</td>'
                } else {
                    tr += '<td><input type="hidden" name="serials[' + productId + ']"><input type="text" onclick="return onlyNumber(event)" name="quantities[' + productId + ']" class="product-quantity form-control text-center" onkeyup="changeQuantity(this)" value="' + quantity + '"></td>'
                }

            tr += '<td><input type="text" readonly name="unit_cost[' + productId + ']" class="product-cost form-control text-center" id="product_costs_' + productId + '" value="' + productCost + '"></td>' +
                '<td><input type="text" onclick="return onlyNumber(event)" name="free_quantities[' + productId + ']" class="free-quantity form-control text-center" value="' + freeQuantity + '"></td>' +
                '<td><input type="text" readonly name="product_sub_total[' + productId + ']" id="product_subtotals_' + productId + '" class="product-row-subtotal form-control text-center" value="' + (productCost * quantity) + '"></td>' +
                '<td class="text-right">' +
                '<button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fa fa-trash"></i></button>'
            '</td>' +
        '</tr>'
        $('.product-display').append(tr)

        setItemSerial()

        // disable selected option
        $('.select-product option[value=' + productId + ']').prop("disabled", true);
        $('.select-quantity').val('');
        $('.select-product').val('');
        $('.select-product').select2('focus');
        $('.select-product').select2();
        loadDatePicker()


        // $('.serials_text_box').tagify({
        //     duplicates :false,
        //     mixTagsInterpolator: ['[[',']]'],
        //     trim:true,
        // });


    } else {
        alert('Select product and fill quantity and free quantity')
    }
}


 // Serial Quantity Show this sector
function showSerialProductPopup(product_id)
{
    $('.product-serial-textarea').val($('#serial_qty_textarea_' + product_id).val())
    $('.total-serial-count').text($('#serial_qty_input_' + product_id).val())

    $('.product-id-holder').val(product_id)
}

function addFinalizeProductSerial()
{
    product_id = $('.product-id-holder').val()


    $('#serial_qty_textarea_' + product_id).val($('.product-serial-textarea').val())
    $('#serial_qty_input_' + product_id).val($('.total-serial-count').text()).keyup()

    let quantity = Number($('.total-serial-count').text() | 0)

    let productCost = Number($('#product_costs_' + product_id).val() | 0)

    $('#product_subtotals_' + product_id).val((quantity * productCost).toFixed(2))


    $('#add_product_serial_modal').modal('hide')

    setItemSerial()
}


function countProductSerial(e, object)
{
    let serials = $(object).val()

    if (serials == '') {
        $('.total-serial-count').text(0)
    } else {
       let serialArray = serials.split(",");
       if(e.keyCode === 13){
            e.preventDefault();
            serialArray.push('');
        }
       let uniqueChars = [...new Set(serialArray)];
       $('.product-serial-textarea').val(uniqueChars);
       $('.total-serial-count').text(uniqueChars.length);
    }

}

function setItemSerial() {
    let total = 0
    $('.item-serial').each(function (index) {
        $(this).text(index+1)
        total += parseFloat($(this).closest('tr').find('.product-row-subtotal').val())
    })
    $('#subtotal').val(total.toFixed(2))
    calculateInvoiceAmount()
}

function removeRow(object) {
    let productId = $(object).closest('tr').find('.product-id').val()
    $('.select-product option[value=' + productId + ']').prop("disabled", false);
    $('.select-product').val('');
    $('.select-product').select2('focus');
    $('.select-product').select2();
    $(object).closest('tr').remove()
    setItemSerial()
}

$('.select-supplier').change(function () {
    let supplier_id = $(this).val()
    if (supplier_id != '') {
        let baseUrl = "/get-supplier-balance/" + supplier_id
        $.ajax({
            url: baseUrl,
            type: 'GET',
            success: function (res) {
                $('#previous_due').val(res.previous_due)
                $('#advance').val(res.advanced_payment)
                calculateInvoiceAmount()
            }
        })
    } else {
        $('#previous_due').val(0)
        $('#advance').val(0)
        calculateInvoiceAmount()
    }
})


$('.select-account').change(function () {
    if ($('.select-account').val() == '') {
        $('.account-balance').text('')
    } else {
        $('.account-balance').text($('.select-account option:selected').data('total-amount'))
    }
})


// prevent given amount is not greater than account balance
$('.given-amount').keyup(function () {
    let amount = $(this).val()
    let accountBalance = $('.account-balance').text()
    if (amount > 0 && accountBalance == '') {
        alert('Please select an account first')
        $(this).val(0)
    } else if (parseFloat(amount) > parseFloat(accountBalance)) {
        alert('Payment amount cant be up to ' + $('.select-account option:selected').text() +  ' account balance')
        $(this).val(0)
    }
})

function changeQuantity(object) {
    let quantity = $(object).val()
    if (quantity == '') {
        $(object).closest('tr').find('.product-row-subtotal').val(0)
    } else {
        let productCost = $(object).closest('tr').find('.product-cost').val()
        $(object).closest('tr').find('.product-row-subtotal').val((parseFloat(quantity) * parseFloat(productCost)).toFixed(2))
    }
    setItemSerial()
}

$('.grand-total-calculate').keyup(function () {
    calculateInvoiceAmount()
})
function calculateInvoiceAmount() {
    let itemTotalAmount = $('#subtotal').val()
    let invoiceDiscount = $('#invoice_discount').val()
    let invoiceTax = $('#invoice_tax').val()
    let advance = $('#advance').val()
    let previousDue = $('#previous_due').val()

    if (itemTotalAmount == '' || isNaN(itemTotalAmount)) {
        itemTotalAmount = 0;
    }
    if (invoiceDiscount == '' || isNaN(invoiceDiscount)) {
        invoiceDiscount = 0;
    }
    if (invoiceTax == '' || isNaN(invoiceTax)) {
        invoiceTax = 0;
    }
    if (advance == '' || isNaN(advance)) {
        advance = 0;
    }
    if (previousDue == '' || isNaN(previousDue)) {
        previousDue = 0;
    }

    let totalPayable = (parseFloat(itemTotalAmount) + parseFloat(invoiceTax) - parseFloat(invoiceDiscount) + parseFloat(previousDue) - parseFloat(advance)).toFixed(2)
    $('#total_payable_temp').val(totalPayable)
}


function loadDatePicker()
{
    $('.expire-date').datepicker({
        format: "dd/mm/yyyy",
        autoclose: true,
        todayHighlight: true,
    });
}
$('.save-btn').click(function () {
    let isSubmit = true
    if ($('.select-supplier').val() == '') {
        alert('Please Select Supplier')
        isSubmit = false
    }
    if ($('.item-serial').length == 0 && isSubmit == true) {
        isSubmit = false
        alert('Please purchase at least one product')
    }

    if ($('.given-amount').val() >= 0 && isSubmit == true) {
        if ($('.account-info').val() == '') {
            isSubmit = false
            alert('Please Select account')
        } else if (parseFloat($('.account-balance').text()) < parseFloat($('.given-amount').val())) {
            alert('Do not have available balance for this account' + parseFloat($('.account-balance').val()) + ' ' + parseFloat($('.given-amount').val()))
            isSubmit = false
        }
    }
    if (isSubmit == true) {
        $('#purchaseForm').submit()
    }
})
