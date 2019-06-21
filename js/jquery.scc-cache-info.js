/*
jquery.scc-cache-info.js
Author: Daisuke Maruyama
Author URI: http://marubon.info/
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/
;jQuery(document).ready(function ($) {
	return $('#scc-dashboard,#scc-dashboard-widget').each(function() {
		$("span[data-scc]").css('display', 'none');

		function isset( data ){
			return ( typeof( data ) != 'undefined' );
		}

		$.ajax({
			url: scc.endpoint + '?action=' + scc.action + '&nonce=' + scc.nonce,
			dataType: 'jsonp',
			cache: false,
			success: function(res) {
				if (res) {
					console.log(res);
					$("span[data-scc='spc']").text(res.share.post_count);
					$("span[data-scc='spfcc']").text(res.share.primary.full_cache_count);
					$("span[data-scc='sppcc']").text(res.share.primary.partial_cache_count);
					$("span[data-scc='spncc']").text(res.share.primary.no_cache_count);
					$("span[data-scc='spcs']").text(res.share.primary.cache_status);

					$("span[data-scc='ssfcc']").text(res.share.secondary.full_cache_count);
					$("span[data-scc='sspcc']").text(res.share.secondary.partial_cache_count);
					$("span[data-scc='ssncc']").text(res.share.secondary.no_cache_count);
					$("span[data-scc='sscs']").text(res.share.secondary.cache_status);

					if ( res.share_delta.twitter > 0) {
						$("span[data-scc='stwitter']").html(res.share_count.twitter + ' (<span class="delta-rise">+' + res.share_delta.twitter + '</span>)');
					} else if (res.share_delta.twitter < 0) {
						$("span[data-scc='stwitter']").html(res.share_count.twitter + ' (<span class="delta-fall">' + res.share_delta.twitter + '</span>)');
					} else {
						$("span[data-scc='stwitter']").html(res.share_count.twitter);
					}
					if (res.share_delta.facebook > 0) {
						$("span[data-scc='sfacebook']").html(res.share_count.facebook + ' (<span class="delta-rise">+' + res.share_delta.facebook + '</span>)');
					} else if (res.share_delta.facebook < 0) {
						$("span[data-scc='sfacebook']").html(res.share_count.facebook + ' (<span class="delta-fall">' + res.share_delta.facebook + '</span>)');
					} else {
						$("span[data-scc='sfacebook']").html(res.share_count.facebook);
					}

					if (res.share_delta.gplus > 0) {
						$("span[data-scc='sgplus']").html(res.share_count.gplus + ' (<span class="delta-rise">+' + res.share_delta.gplus + '</span>)');
					} else if (res.share_delta.gplus < 0) {
						$("span[data-scc='sgplus']").html(res.share_count.gplus + ' (<span class="delta-fall">' + res.share_delta.gplus + '</span>)');
					} else {
						$("span[data-scc='sgplus']").html(res.share_count.gplus);
					}

					if (res.share_delta.pocket > 0) {
						$("span[data-scc='spocket']").html(res.share_count.pocket + ' (<span class="delta-rise">+' + res.share_delta.pocket + '</span>)');
					} else if (res.share_delta.pocket < 0) {
						$("span[data-scc='spocket']").html(res.share_count.pocket + ' (<span class="delta-fall">' + res.share_delta.pocket + '</span>)');
					} else {
						$("span[data-scc='spocket']").html(res.share_count.pocket);
					}

					if (res.share_delta.hatebu > 0) {
						$("span[data-scc='shatebu']").html(res.share_count.hatebu + ' (<span class="delta-rise">+' + res.share_delta.hatebu + '</span>)');
					} else if (res.share_delta.hatebu < 0) {
						$("span[data-scc='shatebu']").html(res.share_count.hatebu + ' (<span class="delta-fall">' + res.share_delta.hatebu + '</span>)');
					} else {
						$("span[data-scc='shatebu']").html(res.share_count.hatebu);
					}

					if (res.share_delta.pinterest > 0) {
						$("span[data-scc='spinterest']").html(res.share_count.pinterest + ' (<span class="delta-rise">+' + res.share_delta.pinterest + '</span>)');
					} else if (res.share_delta.pinterest < 0) {
						$("span[data-scc='spinterest']").html(res.share_count.pinterest + ' (<span class="delta-fall">' + res.share_delta.pinterest + '</span>)');
					} else {
						$("span[data-scc='spinterest']").html(res.share_count.pinterest);
					}

					if (res.share_delta.linkedin > 0) {
						$("span[data-scc='slinkedin']").html(res.share_count.linkedin + ' (<span class="delta-rise">+' + res.share_delta.linkedin + '</span>)');
					} else if (res.share_delta.linkedin < 0) {
						$("span[data-scc='slinkedin']").html(res.share_count.linkedin + ' (<span class="delta-fall">' + res.share_delta.linkedin + '</span>)');
					} else {
						$("span[data-scc='slinkedin']").html(res.share_count.linkedin);
					}

					if (res.share_delta.total > 0) {
						$("span[data-scc='stotal']").html(res.share_count.total + ' (<span class="delta-rise">+' + res.share_delta.total + '</span>)');
					} else if (res.share_delta.total < 0) {
						$("span[data-scc='stotal']").html(res.share_count.total + ' (<span class="delta-fall">' + res.share_delta.total + '</span>)');
					} else {
						$("span[data-scc='stotal']").html(res.share_count.total);
					}

					$("span[data-scc='fpc']").text(res.follow.post_count);
					$("span[data-scc='fpfcc']").text(res.follow.primary.full_cache_count);
					$("span[data-scc='fppcc']").text(res.follow.primary.partial_cache_count);
					$("span[data-scc='fpncc']").text(res.follow.primary.no_cache_count);
					$("span[data-scc='fpcs']").text(res.follow.primary.cache_status);

					$("span[data-scc='fsfcc']").text(res.follow.secondary.full_cache_count);
					$("span[data-scc='fspcc']").text(res.follow.secondary.partial_cache_count);
					$("span[data-scc='fsncc']").text(res.follow.secondary.no_cache_count);
					$("span[data-scc='fscs']").text(res.follow.secondary.cache_status);

					if (isset(res.follow_count)) {
						if (isset(res.follow_delta) && res.follow_delta.twitter > 0) {
							$("span[data-scc='ftwitter']").html(res.follow_count.twitter + ' (<span class="delta-rise">+' + res.follow_delta.twitter + '</span>)');
						} else if (isset(res.follow_delta) && res.follow_delta.twitter < 0) {
							$("span[data-scc='ftwitter']").html(res.follow_count.twitter + ' (<span class="delta-fall">' + res.follow_delta.twitter + '</span>)');
						} else {
							$("span[data-scc='ftwitter']").html(res.follow_count.twitter);
						}
					}

					if (isset(res.follow_count)) {
						if (isset(res.follow_delta) && res.follow_delta.facebook > 0) {
							$("span[data-scc='ffacebook']").html(res.follow_count.facebook + ' (<span class="delta-rise">+' + res.follow_delta.facebook + '</span>)');
						} else if (isset(res.follow_delta) && res.follow_delta.facebook < 0) {
							$("span[data-scc='ffacebook']").html(res.follow_count.facebook + ' (<span class="delta-fall">' + res.follow_delta.facebook + '</span>)');
						} else {
							$("span[data-scc='ffacebook']").html(res.follow_count.facebook);
						}
					}

					if ( isset( res.follow_count ) ) {
						if (isset(res.follow_delta) && res.follow_delta.feedly > 0) {
							$("span[data-scc='ffeedly']").html(res.follow_count.feedly + ' (<span class="delta-rise">+' + res.follow_delta.feedly + '</span>)');
						} else if (isset(res.follow_delta) && res.follow_delta.feedly < 0) {
							$("span[data-scc='ffeedly']").html(res.follow_count.feedly + ' (<span class="delta-fall">' + res.follow_delta.feedly + '</span>)');
						} else {
							$("span[data-scc='ffeedly']").html(res.follow_count.feedly);
						}
					}

					if ( isset( res.follow_count ) ) {
						if (isset(res.follow_delta) && res.follow_delta.push7 > 0) {
							$("span[data-scc='fpush7']").html(res.follow_count.push7 + ' (<span class="delta-rise">+' + res.follow_delta.push7 + '</span>)');
						} else if (isset(res.follow_delta) && res.follow_delta.push7 < 0) {
							$("span[data-scc='fpush7']").html(res.follow_count.push7 + ' (<span class="delta-fall">' + res.follow_delta.push7 + '</span>)');
						} else {
							$("span[data-scc='fpush7']").html(res.follow_count.push7);
						}
					}

					if ( isset( res.follow_count ) ) {
						if (isset(res.follow_delta) && res.follow_delta.instagram > 0) {
							$("span[data-scc='finstagram']").html(res.follow_count.instagram + ' (<span class="delta-rise">+' + res.follow_delta.instagram + '</span>)');
						} else if (isset(res.follow_delta) && res.follow_delta.instagram < 0) {
							$("span[data-scc='finstagram']").html(res.follow_count.instagram + ' (<span class="delta-fall">' + res.follow_delta.instagram + '</span>)');
						} else {
							$("span[data-scc='finstagram']").html(res.follow_count.instagram);
						}
					}

					$(".loading").css('display', 'none');
					$("span[data-scc]").fadeIn();
				} else {
					$("span[data-scc]").text('?');
				}
			},
			error: function(res) {
				$("span[data-scc]").text('?');
			}
		});
	});
});
