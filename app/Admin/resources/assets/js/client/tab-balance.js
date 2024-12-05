function deposit() {
    Swal.fire({
        title: window.foodPunk.i18n.cs_count_message,
        text: window.foodPunk.i18n.deposit,
        input: 'number',
        icon: 'question',
    }).then(function (result) {
        if (result.value) {

            let amount = result.value;

            Swal.fire({
                title: window.foodPunk.i18n.work_in_progress_wait,
                text: window.foodPunk.i18n.message_in_progress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.ajax({
                type: 'POST',
                url: "{{ route('admin.client.deposit') }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    userId: '{{ $client->id }}',
                    amount: amount,
                },
                success: function (result) {
                    location.reload();
                },
            });
        }
    });
}

function withdraw() {
    Swal.fire({
        title: window.foodPunk.i18n.questionnaire_info_withdraw,
        text: window.foodPunk.i18n.questionnaire_info_withdraw_number,
        input: 'number',
        icon: 'question',
    }).then(function (result) {
        if (result.value) {

            let amount = result.value;

            Swal.fire({
                title: window.foodPunk.i18n.work_in_progress_wait,
                text: window.foodPunk.i18n.message_in_progress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.ajax({
                type: 'POST',
                url: "{{ route('admin.client.withdraw') }}",
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    userId: '{{ $client->id }}',
                    amount: amount,
                },
                success: function (result) {
                    location.reload();
                },
            });
        }
    });
}