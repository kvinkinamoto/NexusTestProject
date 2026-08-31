$(document).on('click','.popup_selector',function (event) {
    event.preventDefault();
    var updateID = $(this).attr('data-inputid'); // Btn id clicked
    var elfinderUrl = '/elfinder/popup/';

    // trigger the reveal modal with elfinder inside
    var triggerUrl = elfinderUrl + updateID;
    $.colorbox({
        href: triggerUrl,
        fastIframe: true,
        iframe: true,
        width: '90%',
        height: '90%'
    });

});
// function to update the file selected by elfinder
function processSelectedFile(filePath, requestingField) {
    $('#' + requestingField).val(filePath).trigger('click');
    $('#' + requestingField).val(filePath).trigger('change');
    // Additive for Livewire's wire:model: it binds a real addEventListener,
    // which jQuery's .trigger('input') never reaches — jQuery only calls a
    // native DOM method for event names that have one (click, focus, ...),
    // and 'input' isn't one, so .trigger('input') only ever invoked
    // jQuery-bound handlers. A real dispatchEvent is required.
    var el = document.getElementById(requestingField);
    if (el) {
        el.dispatchEvent(new Event('input', { bubbles: true }));
    }
}
