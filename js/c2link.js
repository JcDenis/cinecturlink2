/*global $, dotclear */
'use strict';

$(function () {
  $('#newlinksearch').on('click', function () {
    this.href = '';
    var val = $('#linktitle').val();
    searchwindow=window.open('http://www.google.com/search?hl='+dotclear.c2_lang+'&q='+val,'search','scrollbars=yes,width=800,height=600,resize=yes,toolbar=yes,menubar=yes');
    searchwindow.focus();
    return false;
  });
  $('#newimagesearch').on('click', function () {
    this.href = '';
    var val = $('#linktitle').val();
    searchwindow=window.open('http://www.amazon.fr/exec/obidos/external-search?keyword='+val+'&mode=blended','search','scrollbars=yes,width=800,height=600,resize=yes,toolbar=yes,menubar=yes');
    searchwindow.focus();
    return false;
  });
  $('#newimageselect').on('change', function() {
    $('#linkimage').attr('value', $(this).val());
  });
});