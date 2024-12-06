function deposit() {
    Swal.fire({
        title: balance.csCountMessage,
        text: balance.deposit,
        input: 'number',
        icon: 'question',
    }).then(function (result) {
        if (result.value) {

            let amount = result.value;

            Swal.fire({
                title: balance.workInProgressWait,
                text: balance.messageInProgress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.ajax({
                type: 'POST',
                url: balance.url,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    userId: balance.userId,
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
        title: balance.questionnaireInfoWithdraw,
        text: balance.questionnaireInfoWithdrawNumber,
        input: 'number',
        icon: 'question',
    }).then(function (result) {
        if (result.value) {

            let amount = result.value;
            Swal.fire({
                title: balance.workInProgressWait,
                text: balance.messageInProgress,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.ajax({
                type: 'POST',
                url: urlWithdraw,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    userId: balance.userId,
                    amount: amount,
                },
                success: function (result) {
                    location.reload();
                },
            });
        }
    });
}