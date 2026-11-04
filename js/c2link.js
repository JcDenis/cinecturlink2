/*global $, dotclear */
'use strict';

$(() => {
  const data = dotclear.getData('c2link');

  $('#newlinksearch').on('click', function () {
    this.href = '';
    const val = encodeURIComponent($('#linktitle').val());
    const searchwindow = window.open(
      `https://www.google.com/search?hl=${data.lang}&q=${val}`,
      'search',
      'scrollbars=yes,width=800,height=600,resize=yes,toolbar=yes,menubar=yes',
    );
    searchwindow?.focus();
    return false;
  });
  $('#newimagesearch').on('click', function () {
    this.href = '';
    const val = encodeURIComponent($('#linktitle').val());
    const searchwindow = window.open(
      `https://www.amazon.fr/exec/obidos/external-search?keyword=${val}&mode=blended`,
      'search',
      'scrollbars=yes,width=800,height=600,resize=yes,toolbar=yes,menubar=yes',
    );
    searchwindow?.focus();
    return false;
  });
  $('#newimageselect').on('change', function () {
    $('#linkimage').attr('value', $(this).val());
  });
});
