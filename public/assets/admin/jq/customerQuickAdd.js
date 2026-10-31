    //
    // // Customer Create Section
    //
    // function resetCustomerForm()
    // {
    //     $('.customerAddForm').trigger('reset');
    // }
    //
    // $('.customerAddForm').submit(function (e) {
    //     e.preventDefault();
    //
    //     let that = $(this)
    //     triggerPreloader(true)
    //
    //     window.axios.post(`${that.attr('action')}`, that.serialize())
    //         .then(({data}) => {
    //             triggerPreloader(false)
    //             $('#addnew').modal('hide')
    //             setCustomer(data.customer)
    //             resetDue()
    //         })
    //         .catch((error) => {
    //             triggerPreloader(false)
    //             const errors = error.response.data.errors
    //             $.each(errors, (i, error) => {
    //                 $('.error-ul').append(` <li class="text-danger">${error[0]}</li> `)
    //                 that.delegate(':input', 'focus', function () {
    //                     $('.error-ul li').remove();
    //                 })
    //             })
    //         });
    // })
    //
    // function resetDue()
    // {
    //     $('#previous_due').val(0)
    //     $('#advanced_payment').val(0)
    // }
    //
    // function triggerPreloader(isLoading = false){
    //     if(isLoading){
    //         $('.loading').css('display', 'flex')
    //     }else{
    //         $('.loading').css('display', 'none')
    //     }
    // }
    //

    // Customer Create Section

    function resetCustomerForm()
    {
        $('.customerAddForm').trigger('reset');
    }

    $('.customerAddForm').submit(function (e) {
        e.preventDefault()
        let that = $(this)
        triggerPreloader(true)
        window.axios.post(`${that.attr('action')}`, that.serialize())
            .then(({data}) => {
                loadLastCustomer()
                let customer_name = $('.quick-add-customer-name').val()
                triggerPreloader(false)
                $('#addnew').modal('hide')
                
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Customer Added Successfully!',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
                
                setCustomer(data.customer)
                resetDue()
            })
            .catch((error) => {
                triggerPreloader(false)
                const errors = error
                $.each(errors, (i, error) => {
                    $('.error-ul').append(` <li class="text-danger">${error[0]}</li> `)
                    that.delegate(':input', 'focus', function () {
                        $('.error-ul li').remove();
                    })
                })
            })
    });

    function resetDue()
    {
        $('#previous_due').val(0)
        $('#advanced_payment').val(0)
    }

    function triggerPreloader(isLoading = false){
        if(isLoading){
            $('.loading').css('display', 'flex')
        }else{
            $('.loading').css('display', 'none')
        }
    }

    function loadLastCustomer()
    {
        $.get('/people/get-last-added-customer', function(res) {
            $('#customer').val(res.name)
            
            // Append to Select2 if not exists, and select it
            if ($(".selected-customer option[value='" + res.id + "']").length == 0) {
                var newOption = new Option(res.formatted_name || res.name, res.id, true, true);
                $(newOption).attr('data-customer-area', res.area_name || '');
                $('.selected-customer').append(newOption).trigger('change');
            } else {
                $('.selected-customer').val(res.id).trigger('change');
            }
            
            $('.previous-due').val(res.current_balance)
        })
    }


