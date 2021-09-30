$(function (){
    $('.post-block-btn').on('click',function (){
        var name = $(this).data('cssfilename');
        var path = $(this).data('cssfilepath');
        var blockCssID = 'post-block-css-' + name;
        var $iframe = $('.cke_contents').find('iframe').contents();

        if($iframe.find('#'+ blockCssID).length <= 0) {
            var htm = '<link id="'+ blockCssID+'" type="text/css" rel="stylesheet" href="'+ path +'">';
            $iframe.find('head').append(htm);
        }

    });
});
