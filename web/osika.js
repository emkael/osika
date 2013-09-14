function getHand(hand) {
    jQuery('#evaluate').val('Czekaj...');
    jQuery('#evaluate').attr('disabled', 'disabled');
    jQuery.getJSON(
        'osika.php',
        {
            'h': hand,
            'f': 'json'
        },
        function (data) {
            jQuery('#evaluate').val('Oceń');
            jQuery('#evaluate').removeAttr('disabled');
            if (data.error) {
                jQuery('#result').html('<div class="error">'+data.error+'</div>');
            }
            else {
                var html = '<table cellpadding="0" cellspacing="0"><tr><th></th><th class="spades">&spades;</th><th class="hearts">&hearts;</th><th class="diamonds">&diams;</th><th class="clubs">&clubs;</th><th>&Sigma;</th></tr>';
                html += '<tr><td>Honory (lewy)</td><td>'+data.lh[0]+'</td><td>'+data.lh[1]+'</td><td>'+data.lh[2]+'</td><td>'+data.lh[3]+'</td><td class="subtotal">'+data.lh.total+'</td></tr>';
                html += '<tr><td>Poprawka za zgrupowania honorów</td><td>'+data.lh_plus[0]+'</td><td>'+data.lh_plus[1]+'</td><td>'+data.lh_plus[2]+'</td><td>'+data.lh_plus[3]+'</td><td class="subtotal">'+data.lh_plus.total+'</td></tr>';
                html += '<tr><td>Poprawka za podwiązania honorów</td><td>'+data.lh_10[0]+'</td><td>'+data.lh_10[1]+'</td><td>'+data.lh_10[2]+'</td><td>'+data.lh_10[3]+'</td><td class="subtotal">'+data.lh_10.total+'</td></tr>';
                html += '<tr><td>Poprawka za krótkie honory</td><td>'+data.lh_short[0]+'</td><td>'+data.lh_short[1]+'</td><td>'+data.lh_short[2]+'</td><td>'+data.lh_short[3]+'</td><td class="subtotal">'+data.lh_short.total+'</td></tr>';
                html += '<tr class="subtotal"><td>Lewy honorowe</td><td>'+(data.subtotal[0]-data.lu[0])+'</td><td>'+(data.subtotal[1]-data.lu[1])+'</td><td>'+(data.subtotal[2]-data.lu[2])+'</td><td>'+(data.subtotal[3]-data.lu[3])+'</td><td>'+(data.subtotal.total-data.lu.total)+'</td></tr>';
                html += '<tr><td>Lewy układowe</td><td>'+data.lu[0]+'</td><td>'+data.lu[1]+'</td><td>'+data.lu[2]+'</td><td>'+data.lu[3]+'</td><td class="subtotal">'+data.lu.total+'</td></tr>';
                html += '<tr class="subtotal"><td>Razem</td><td>'+data.subtotal[0]+'</td><td>'+data.subtotal[1]+'</td><td>'+data.subtotal[2]+'</td><td>'+data.subtotal[3]+'</td><td>'+data.subtotal.total+'</td></tr>';
                html += '<tr><td>Poprawka za lewy szybkie</td><td colspan="5">'+data.lsz.total+'</td></tr>';
                html += '<tr><td>Poprawka za wysokie blotki</td><td colspan="5">'+data.lu_plus.total+'</td></tr>';
                html += '<tr><td>Poprawka za kolory krótkie</td><td colspan="5">'+data.short_suit.total+'</td></tr>';
                html += '<tr><td>Poprawka za kolory starsze</td><td colspan="5">'+data.major_suit.total+'</td></tr>';
                html += '<tr><td>Poprawka za lokalizację</td><td colspan="5">'+data.l10n.total+'</td></tr>';
                html += '<tr class="total"><td>Łącznie</td><td colspan="5">'+data.total.total+'</td></tr>';
                html += '</table>';
                jQuery('#result').html(html);
		location.hash = hand;
            }
        }
    );
}

jQuery(document).ready(function () {
    jQuery('#evaluate').click(function () {
        var hand = jQuery('#suit0').val()+'|'+jQuery('#suit1').val()+'|'+jQuery('#suit2').val()+'|'+jQuery('#suit3').val();
        getHand(hand);
        return false;
    });
    if (location.hash) {
        var hand = location.hash.substring(1).split('|');
        for (h = 0; h < hand.length; h++) {
            jQuery('#suit'+h).val(hand[h]);
        }
        getHand(location.hash.substring(1));
    }
});