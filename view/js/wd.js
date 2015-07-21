function msg(texto, titulo) {
    $.Zebra_Dialog('<strong>' + texto + '</strong>', {
        'type': 'question',
        'title': titulo,
        'buttons': ['OK'],
        'onClose': function (caption) {
            if (caption == 'OK') {
                
            }
        }});
}