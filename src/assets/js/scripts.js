jQuery(function ($) {
    $(document).on('ready', function () {
        /**
         * Size box
         * @constructor
         */
        function ActiveBox() {
            this.$wrapper = $("#tp-dashboard-active");
            this.active = function () {
                this.$wrapper.addClass('active');
                this.$wrapper.find('input').prop('disabled', true);
            };
            this.deactivate = function () {
                this.$wrapper.removeClass('active');
                this.$wrapper.find('input').prop('disabled', false);
            };
            this.loading = function(){
                this.$wrapper.find('.spinner').addClass('is-active');
            };
            this.hideLoading = function(){
                this.$wrapper.find('.spinner').removeClass('is-active');
            };
            return this;
        }

        var ActiveBox = new ActiveBox();

        $(document).on('click', "#tp-dashboard-active button#active-key", function () {
            var email = $("#tpdb-email").val();
            var key = $("#tpdb-key").val();
            ActiveBox.loading();
            $.ajax({
                type: 'POST',
                url: tp_dashboard_admin_js.ajax_url,
                data: {
                    action: 'validate_service',
                    key: key,
                    email: email
                },
                success: function (response) {
                    console.log(response);
                    if (response.success && response.data.success) {
                        ActiveBox.active();
                    } else{
                        $(".notify-error").fadeIn();
                        setTimeout(function () {
                            $(".notify-error").fadeOut();
                        }, 3600);
                    }
                },
                error: function (e) {
                    console.log(e);
                },
                complete:function(response){
                    ActiveBox.hideLoading();
                }
            });
        });

        $(document).on('click', "#tp-dashboard-active button#change-key", function () {
            ActiveBox.deactivate();
        });
    });
});