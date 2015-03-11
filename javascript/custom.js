YUI().use('node', 'event', function(Y) {
    Y.on('domready', function () {
    	Y.all('.eplist li').each(function (node) {
    	  if (!node.one('a')) {
    	      node.addClass('empty');
    	  }
   	 });
    });
});

