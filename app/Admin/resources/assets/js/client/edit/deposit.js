export function initDeposit() {
    window.FoodPunk.functions.deposit = function () {
        Swal.fire({
            title: window.FoodPunk.i18n.deposit,
            text: window.FoodPunk.i18n.csCountMessage,
            input: 'number',
            icon: 'question',
        }).then(function (result) {
            if (result.value) {

                let amount = result.value;

                Swal.fire({
                    title: window.FoodPunk.i18n.wait,
                    text: window.FoodPunk.i18n.inProgress,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: 'POST',
                    url: window.FoodPunk.route.clientDeposit,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.pageInfo.clientId,
                        amount: amount,
                    },
                    success: function (result) {
                        location.reload();
                    },
                });
            }
        });
    }
    window.FoodPunk.functions.withdraw = function () {
        Swal.fire({
            title: window.FoodPunk.i18n.infoWithdraw,
            text: window.FoodPunk.i18n.infoWithdrawNumber,
            input: 'number',
            icon: 'question',
        }).then(function (result) {
            if (result.value) {

                let amount = result.value;

                Swal.fire({
                    title: window.FoodPunk.i18n.wait,
                    text: window.FoodPunk.i18n.inProgress,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });

                $.ajax({
                    type: 'POST',
                    url: window.FoodPunk.route.clientWithdraw,
                    dataType: 'json',
                    data: {
                        _token: $('meta[name=csrf-token]').attr('content'),
                        userId: window.FoodPunk.pageInfo.clientId,
                        amount: amount,
                    },
                    success: function (result) {
                        location.reload();
                    },
                });
            }
        });
    }
}