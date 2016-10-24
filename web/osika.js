var Osika = function() {
    var that = this;

    that.resultTemplate = jQuery('#resultTemplate').remove().html();

    that.handleData = function(data) {
		jQuery('#evaluate').val('Oceń');
		jQuery('#evaluate').removeAttr('disabled');
		if (data.error) {
            jQuery('#result').html('<div class="error">'+data.error+'</div>');
		}
		else {
			var html = Mustache.to_html(that.resultTemplate, data);
            jQuery('#result').html(html);
		}
    };

    that.getHand = function(hand) {
		jQuery('#evaluate').val('Czekaj...');
		jQuery('#evaluate').attr('disabled', 'disabled');
		jQuery.getJSON(
            'osika.php',
            {
				'h': hand,
				'f': 'json'
            },
			that.handleData
		);
		location.hash = hand;
    };

    that.init = function() {
		jQuery('#evaluate').unbind('click').click(function () {
            var hand = jQuery('#suit0').val()+'|'+jQuery('#suit1').val()+'|'+jQuery('#suit2').val()+'|'+jQuery('#suit3').val();
            that.getHand(hand);
            return false;
		});
		if (location.hash) {
            var hand = location.hash.substring(1).split('|');
            for (h = 0; h < hand.length; h++) {
				jQuery('#suit'+h).val(hand[h]);
            }
            that.getHand(location.hash.substring(1));
		}
		jQuery(window).hashchange(that.init);
    };
    that.init();
};

jQuery(document).ready(function () {
    var o = new Osika();
});
