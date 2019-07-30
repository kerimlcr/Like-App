$ = jQuery;
$(document).ready(function() {
  $('#tags_table').DataTable({
    searching: false,
    aLengthMenu: [[5, 10, -1], [5, 10, "All"]],
    order: [[ 1, "desc" ]],
    info: false
  });
});
