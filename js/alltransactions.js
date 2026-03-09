   $(document).ready(function() {

            // ================================
            // MEMBER SELECTION
            // ================================
            $('#global_member_id').on('change', function() {

                let mid = $(this).val();
                let memberType = $("#global_member_id option:selected").data("type");

                if (!mid) return;

      

                // Set hidden member_id for deposit
                $("#deposit_member_id").val(mid);

                // Reset deposit dropdown
                $("#deposit_type").val("");

                // 🔥 Deposit Type Logic
                if (memberType === "associate") {
                    $("#deposit_type option[value='capital_share']").prop("disabled", true).hide();
                    $("#deposit_type option[value='savings']").prop("disabled", false).show();
                } else if (memberType === "regular") {
                    $("#deposit_type option[value='capital_share']").prop("disabled", false).show();
                    $("#deposit_type option[value='savings']").prop("disabled", false).show();
                }

                // ================================
                // Load Member Summary
                // ================================
                $('#member_summary_panel')
    .html('<div class="text-center p-20">Loading...</div>')
    .load('ajax_get_member_summary.php?member_id=' + mid);

                $.getJSON('ajax_get_member_transactions.php', {
                    member_id: mid
                }, function(data) {

                    let html = '<option value="">-- Select Transaction --</option>';

                    if (data.length === 0) {
                        html += '<option value="">No transactions found</option>';
                    } else {
                        data.forEach(function(t) {
                            html += `
                <option value="${t.transaction_id}">
                    [${t.type_name}] ₱${parseFloat(t.amount).toFixed(2)} 
                    - Ref: ${t.reference_no}
                    - ${t.transaction_date}
                </option>
            `;
                        });
                    }

                    $('#void_transaction_select').html(html);
                });

                // ================================
                // Load Withdraw Requests
                // ================================
                $.getJSON('ajax_get_withdraw_requests.php', {
                    member_id: mid
                }, function(data) {
                    let html = '<option value="">-- Select Request --</option>';
                    $.each(data, function(i, item) {
                        html += `<option value="${item.request_id}" data-amount="${item.amount}">
                    Request #${item.request_id} - ₱${item.amount}
                </option>`;
                    });
                    $('#withdraw_request_select').html(html);
                });

                // ================================
                // Load Loan Schedules (auto-select next schedule)
                // ================================
                $.getJSON('ajax_get_schedules.php', {
                    member_id: mid
                }, function(data) {
                    if (!data || data.length === 0) {
                        $('#schedule_select').html('<option value="">No schedules found</option>');
                        return;
                    }

                    // Sort schedules by due_date ascending
                    data.sort((a, b) => new Date(a.due_date) - new Date(b.due_date));

                    let html = '';
                    let nextSchedule = null;

                    $.each(data, function(i, item) {
                        html += `<option value="${item.schedule_id}" 
            data-loan="${item.loan_id}" 
            data-account="${item.account_id}" 
            data-amount="${item.total_due}" 
            data-total-due-full="${item.full_remaining}">
            Due: ${item.due_date} (₱${item.total_due})
        </option>`;

                        // pick the first schedule that has remaining due
                        if (!nextSchedule && item.total_due > 0) {
                            nextSchedule = item;
                        }
                    });

                    $('#schedule_select').html(html);

                    if (nextSchedule) {
                        // Auto-select next schedule
                        $('#schedule_select').val(nextSchedule.schedule_id);
                        $('#loan_id_hidden').val(nextSchedule.loan_id);
                        $('#account_id_hidden').val(nextSchedule.account_id);
                        $('#loan_amount_input').val(nextSchedule.total_due.toFixed(2));
                        $('#schedule_select').prop('disabled', false); // allow changing if needed
                    }
                });

            }); // end member_id change


            let loanPaymentData = {};

            function updateLoanAmount() {
                let sel = $('#schedule_select').find(':selected');
                let mode = $('#pay_mode').val();

                if (!sel.val()) return;

                // Set hidden inputs for PHP
                $('#loan_id_hidden').val(sel.data('loan') || '');
                $('#account_id_hidden').val(sel.data('account') || '');
                $('#schedule_id_hidden').val(sel.val() || 0);

                if (mode === 'scheduled') {
                    $('#loan_amount_input').val(sel.data('amount') || '').prop('readonly', true);
                } else if (mode === 'full') {
                    $('#loan_amount_input').val(sel.data('total-due-full') || sel.data('amount') || 0).prop('readonly', true);
                } else if (mode === 'custom') {
                    $('#loan_amount_input').prop('readonly', false).val('');
                }
            }

            $('#pay_mode').on('change', function() {
                const mode = $(this).val();
                let sel = $('#schedule_select').find(':selected');

                if (mode === 'full') {
                    if (!sel.val()) return;

                    // Use full_remaining of the selected schedule
                    let total = parseFloat(sel.data('total-due-full')) || parseFloat(sel.data('amount')) || 0;

                    $('#loan_amount_input').val(total.toFixed(2)).prop('readonly', true);
                    $('#schedule_select').prop('disabled', true);
                    $('#sched_wrapper').hide();
                    $('#schedule_select').prop('required', false);
                } else if (mode === 'custom') {
                    $('#loan_amount_input').prop('readonly', false).val('');
                    $('#sched_wrapper').hide();
                    $('#schedule_select').prop('required', false);
                } else { // scheduled
                    $('#sched_wrapper').show();
                    $('#schedule_select').prop('disabled', false).prop('required', true);
                    updateLoanAmount(); // auto-set amount to selected schedule
                }
            });
            $('#schedule_select').on('change', function() {
                let sel = $(this).find(':selected');
                if (!sel.val()) return;

                $('#loan_id_hidden').val(sel.data('loan') || '');
                $('#account_id_hidden').val(sel.data('account') || '');

                if ($('#pay_mode').val() === 'scheduled') {
                    $('#loan_amount_input').val(sel.data('amount') || 0).prop('readonly', true);
                }
            });
            // Submit loan payment form
            $(document).on('submit', '#loanPaymentForm', function(e) {
                e.preventDefault();

                let amount = parseFloat($('#loan_amount_input').val() || 0);
                let loanId = $('#loan_id_hidden').val();
                let accountId = $('#account_id_hidden').val();
                let memberName = $("#global_member_id option:selected").text();
                let payMode = $('#pay_mode').val();
                let scheduleText = $('#schedule_select option:selected').text();

                if (!accountId || accountId == 0) {
                    return $.jGrowl("Selected schedule has no valid account assigned.", {
                        theme: 'bg-danger'
                    });
                }
                if (!loanId) return $.jGrowl("Select a loan schedule first.", {
                    theme: 'bg-danger'
                });
                if (amount <= 0) return $.jGrowl("Enter a valid amount.", {
                    theme: 'bg-danger'
                });

                // Store data for confirmation
                loanPaymentData = $(this).serialize();

                // Show confirmation modal
                $('#loan_modal_member').text(memberName);
                $('#loan_modal_mode').text(payMode.charAt(0).toUpperCase() + payMode.slice(1));
                $('#loan_modal_amount').text(amount.toLocaleString());
                $('#loan_modal_schedule').text(scheduleText);
                $('#loanConfirmModal').modal('show');
            });

            // Confirm loan payment modal
            $('#confirmLoanPayment').on('click', function() {
                let $btn = $(this).prop('disabled', true).text('Processing...');

                $.ajax({
                    url: '../transaction.php',
                    type: 'POST',
                    data: loanPaymentData,
                    dataType: 'json',
                    success: function(res) {
                        $('#loanConfirmModal').modal('hide');
                        if (res.success) {
                            $.jGrowl(`Loan payment successful! Ref: ${res.reference}`, {
                                theme: 'bg-success'
                            });
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            $.jGrowl(res.message || 'Payment failed.', {
                                theme: 'bg-danger'
                            });
                            $btn.prop('disabled', false).text('Confirm Payment');
                        }
                    },
                    error: function() {
                        $('#loanConfirmModal').modal('hide');
                        $.jGrowl('Server error occurred.', {
                            theme: 'bg-danger'
                        });
                        $btn.prop('disabled', false).text('Confirm Payment');
                    }
                });
            });
            // ================================
            // WITHDRAW LOGIC
            // ================================
            let withdrawActionType = "";

            $('#withdraw_request_select').on('change', function() {
                let sel = $(this).find(':selected');
                $('#requested_amount').val(sel.data('amount') || '');
                $('#withdraw_request_id').val(sel.val() || '');
            });

            function showWithdrawModal(action) {
                let request_id = $('#withdraw_request_id').val();
                let amount = $('#requested_amount').val();
                let memberName = $("#global_member_id option:selected").text();

                if (!request_id) return $.jGrowl('Select a request first', {
                    theme: 'bg-danger'
                });

                withdrawActionType = action;

                $("#modal_member_name").text(memberName);
                $("#modal_request_id").text(request_id);
                $("#modal_amount").text(parseFloat(amount).toLocaleString());
                $("#modal_action_text")
                    .toggleClass("text-success", action === "approve_withdrawal")
                    .toggleClass("text-danger", action === "reject_withdrawal")
                    .text(action === "approve_withdrawal" ? "Are you sure you want to APPROVE this withdrawal?" : "Are you sure you want to REJECT this withdrawal?");

                $("#withdrawConfirmModal").modal("show");
            }

            $('#approve-btn').on('click', function(e) {
                e.preventDefault();
                showWithdrawModal('approve_withdrawal');
            });
            $('#reject-btn').on('click', function(e) {
                e.preventDefault();
                showWithdrawModal('reject_withdrawal');
            });

            $('#confirmWithdrawAction').on('click', function() {
                let request_id = $('#withdraw_request_id').val();
                if (!request_id) return;

                $.ajax({
                    url: '../transaction.php',
                    type: 'POST',
                    data: {
                        action_type: withdrawActionType,
                        request_id: request_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        $("#withdrawConfirmModal").modal("hide");
                        if (res.status === 'success') {
                            $.jGrowl(withdrawActionType === "approve_withdrawal" ? "Withdrawal approved!" : "Withdrawal rejected!", {
                                theme: 'bg-success'
                            });
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            $.jGrowl(res.message || 'Action failed.', {
                                theme: 'bg-danger'
                            });
                        }
                    },
                    error: function() {
                        $.jGrowl('Server error occurred.', {
                            theme: 'bg-danger'
                        });
                    }
                });
            });

            // ================================
            // DEPOSIT LOGIC
            // ================================
            let depositData = {};

            $(document).on("submit", "#deposit-form", function(e) {
                e.preventDefault();
                let $form = $(this);
                let $submitBtn = $form.find(':input[type="submit"]');

                if ($submitBtn.prop("disabled")) return;

                let depositType = $("#deposit_type").val();
                if (!depositType) return $.jGrowl("Please select deposit type.", {
                    theme: "bg-danger"
                });

                let amount = $form.find('input[name="amount"]').val();
                let memberName = $("#global_member_id option:selected").text();

                depositData = $form.serialize();
                depositData += depositType === "savings" ? "&save-savings=1" : "&save-capital-share=1";

                $("#deposit_modal_member").text(memberName);
                $("#deposit_modal_type").text($("#deposit_type option:selected").text());
                $("#deposit_modal_amount").text(parseFloat(amount).toLocaleString());
                $("#depositConfirmModal").modal("show");
            });

            $('#confirmDepositAction').on('click', function() {
                let $submitBtn = $("#deposit-form").find(':input[type="submit"]');
                $submitBtn.prop("disabled", true).html("Processing...");

                $.ajax({
                    type: "POST",
                    url: "../transaction.php",
                    data: depositData,
                    dataType: "text",
                    success: function(res) {
                        $("#depositConfirmModal").modal("hide");
                        if (res.trim() === "1") {
                            $.jGrowl($("#deposit_type option:selected").text() + " deposit successful!", {
                                theme: "bg-success"
                            });
                            setTimeout(() => location.reload(), 1200);
                        } else {
                            $.jGrowl(res, {
                                theme: "bg-danger"
                            });
                            $submitBtn.prop("disabled", false).html("Process Deposit");
                        }
                    },
                    error: function() {
                        $("#depositConfirmModal").modal("hide");
                        $.jGrowl("Something went wrong.", {
                            theme: "bg-danger"
                        });
                        $submitBtn.prop("disabled", false).html("Process Deposit");
                    }
                });
            });

            $('#btnConfirmVoid').on('click', function() {

                let transaction_id = $('#void_transaction_select').val();
                let reason = $('#void_reason').val().trim();

                if (!transaction_id) {
                    alert("Please select a transaction.");
                    return;
                }

                if (!reason) {
                    alert("Please provide a reason.");
                    return;
                }

                if (!confirm("Are you sure you want to void this transaction?")) {
                    return;
                }

                $.ajax({
                    url: '../transaction.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action_type: 'void_transaction',
                        transaction_id: transaction_id,
                        reason: reason
                    },
                    success: function(res) {

                        if (res.status === 'success') {
                            alert("Transaction successfully voided.");
                            location.reload();
                        } else {
                            alert(res.message);
                        }
                    },
                    error: function() {
                        alert("Server error.");
                    }
                });
            });

        });