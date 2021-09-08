/*
$(function() {
  // checkboxes selection
  $('.checkboxes-helpers').each(function() {
    dotclear.checkboxesHelpers(this);
  });
});
*/
$(function () {
  $('.checkboxes-helpers').each(function () {
    dotclear.checkboxesHelpers(this, undefined, '#form-entries td input[type=checkbox]', '#form-entries #do-action');
  });
  $('#form-entries td input[type=checkbox]').enableShiftClick();
  dotclear.condSubmit('#form-entries td input[type=checkbox]', '#form-entries #do-action');
  dotclear.postsActionsHelper();
  dotclear.responsiveCellHeaders(document.querySelector('#form-entries table'), '#form-entries table', 1);
});