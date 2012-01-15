amplify.request.define('folder', 'ajax', {
	'url' : '/rest/v1/folder/{path}',
	'type': 'GET',
	'decoder': 'jsend'
});
amplify.request.define('metadata', 'ajax', {
	'url' : '/rest/v1/metadata/{path}',
	'type': 'GET',
	'decoder': 'jsend'
});

(function(app, undefined) {
	window.app = {
		init: function() {
			this.navigate( window.location.pathname );
		},
		navigate: function(path) {
			if ( path.substr(0, 1) == '/' ) {
				path = path.substr(1);
			}
			amplify.request('folder', { path: path }, function(data) {
				amplify.publish('folders', {
					path:    data.path,
					folders: data.folders
				});
				amplify.publish('files', {
					path:  data.path,
					files: data.files
				});
			});
		},
		imageUrl: function(image) {
			return '/library' + ( image.substr(0, 1) != '/' ? '/' : '' ) + image;
		},
		thumbnailUrl: function(image, size) {
			return image.replace(/(.*)(\.[a-z0-9]{2,4})/, '$1x' + size + '-' + size + '$2');
		}
	};
	
	
})(window.app = window.app || {});

amplify.subscribe('folders', function(data) {
	var $folders = $('div.folders'),
	    $paths   = $folders.find('> .breadcrumbs'),
	    $gallery = $folders.find('> .gallery');
	
	var $nav = $('header > nav');
	$nav.find('li:not(.ignore)').remove();
	
	var path = '';
	$.each(data.path, function(k, v) {
		if ( v == '' ) {
			return;
		}
		path += '/' + v;
		$nav.append( '<li><a href="' + path + '">' + v + '</a>&nbsp;&raquo;</li>');
	});
	
	if ( !data.folders || data.folders.length == 0 ) {
		if ( $folders.is(':visible') ) {
			$folders.slideUp().fadeOut();
		}
		return;
	}
	$folders.fadeIn(1500).slideDown(2000);
	
	$gallery.empty();
	$.each(data.folders, function(k, v) {
		var url  = path + ( path != '/' ? '/' : '' ) + v;
		var html = '<div class="entry"><a href="' + url  + '"><img src="' + app.thumbnailUrl(app.imageUrl(url) + '/_.jpg', 50) + '"><span>' + v + '</span></a></div>';
		$gallery.append(html);
	});
});

amplify.subscribe('image.active', function(data) {
	$('div.ribbon div.entry').removeClass('active').find('> a[href="' + data.image + '"]').parent().addClass('active');
});
amplify.subscribe('files', function(data) {
	var $ribbon = $('div.ribbon'),
	    $images = $ribbon.find('> .images');
	
	if ( !data.files || data.files.length == 0 ) {
		if ( $ribbon.is(':visible') ) {
			$ribbon.slideUp().fadeOut();
		}
		return;
	}
	$ribbon.fadeIn(1500).slideDown(2000, function() {
		amplify.publish('layout');
	});
	
	var path = data.path.join('/');
	$images.empty();
	$.each(data.files, function(k, v) {
		var url = path + '/' + v;
		var html = '<div class="entry"><a href="' + url  + '" class="action-image"><img src="' + app.thumbnailUrl( app.imageUrl(url), 150) + '"><span>' + v.replace(/(\.[a-z0-9]{2,4})$/, '') + '</span></a></div>';
		$images.append(html);
	});
	$images.find('div.entry').filter(':eq(0)').addClass('focus');
});

amplify.subscribe('image.feature', function(data) {
	console.log('IMAGE: ' + app.thumbnailUrl( app.imageUrl(data.image), 800 ));
	var $img = $('div.preview > img.feature');
	$img.hide();
	$img.one('load', function() {
		amplify.publish('image.active', data);
		$(this).fadeIn();
	}).attr('src', app.thumbnailUrl( app.imageUrl(data.image), 800 ));
});

amplify.subscribe('image.active', function(data) {
	var $metadata = $('div.preview div.metadata');
	$metadata.html('Loading...');
	amplify.request('metadata', { path: data.image }, function(data) {
		$metadata.text( JSON.stringify(data) );
	});
});

amplify.subscribe('ribbon.focus', function(data) {
	var $images = $('div.ribbon div.images');
	var offset = $images.offset();
	
	console.log('FOCUS');
	//console.dir(data);
	$('div.ribbon div.entry').filter('.focus').removeClass('focus');
	var $focus = $('div.ribbon div.entry a[href="' + data.image + '"]').closest('div.entry').addClass('focus');
	var off = $focus.offset();
	var newLeft = ( ( off.left - offset.left ) * -1 ) + 30;
	var time = 200 + ( Math.abs( ( offset.left - off.left ) / $focus.width() ) * 100 );
	time = 1000;
	$images.animate({ 'left': newLeft }, time);
});

amplify.subscribe('layout', function(data) {
	var $preview = $('div.preview');
	var $header  = $('header');
	var $footer  = $('footer');
	var $ribbon  = $('div.ribbon');
	$preview.height( $(window).height() - $ribbon.outerHeight() - $header.outerHeight() - $footer.outerHeight() - 100 );
});

$(document).delegate('.action-image', 'click', function(evt) {
	evt.stopPropagation();
	evt.preventDefault();
	amplify.publish('image.feature', { 
		image: $(this).attr('href')
	});
	return false;
});

$(document).delegate('a.left,a.right', 'click', function(evt) {
	evt.stopPropagation();
	evt.preventDefault();
	var focus;
	if ( $(this).is('.right') ) {
		focus = $('div.ribbon div.entry').filter('div.focus').next().find('a.action-image').attr('href');
	} else {
		focus = $('div.ribbon div.entry').filter('div.focus').prev().find('a.action-image').attr('href');
	}
	if ( focus ) {
		amplify.publish('ribbon.focus', { image: focus });
	}
	return false;
});

$(document).bind('keyup', function(evt) {
	if ( evt.which == 39 || evt.which == 37 ) {
		var $entry = $('div.ribbon div.entry');
		var $next  = $entry.filter('.active');
		
		if ( $next.length == 0 ) {
			$next = $entry.eq(0);
		} else if ( evt.which == 39 ) {
			$next = $next.next();
		} else if ( evt.which == 37 ) {
			$next = $next.prev();
		}
		
		$next.find('a.action-image').trigger('click');
	}
});

app.init();
