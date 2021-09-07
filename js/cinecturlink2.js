/*global $, dotclear */
'use strict';

$(function () {
  $('#c2itemslist').sortable({
    cursor: 'move',
  });
  $('#c2itemslist tr')
    .on('mouseenter', function () {
      $(this).css({
        cursor: 'move',
      });
    })
    .on('mouseleave', function () {
      $(this).css({
        cursor: 'auto',
      });
    });
  $('#c2items').on('submit', function () {
    let order = [];
    $('#c2itemslist tr td input.position').each(function () {
      order.push(this.name.replace(/^order\[([^\]]+)\]$/, '$1'));
    });
    $('input[name=im_order]')[0].value = order.join(',');
    return true;
  });
  $('#c2itemslist tr td input.position').hide();
  $('#c2itemslist tr td.handle').addClass('handler');
  dotclear.condSubmit('#c2items tr td input[name^=items_selected]', '#c2items #remove-action');
  dotclear.responsiveCellHeaders(document.querySelector('#c2items table'), '#c2items table', 2);
});