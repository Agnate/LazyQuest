$(document).ready(function () {

  var $output = $('#output');
  var $command = $('#text');

  // Add button listeners to stuff that already exists.
  updateListeners();

  // Intercept the form being submitted.
  $("#interface-form").submit(function (e) {
    var postData = $(this).serializeArray();
    var formURL = $(this).attr("action");

    $.ajax({
      url : formURL,
      type: "POST",
      data : postData,
      success: function(data, textStatus, jqXHR) {
        // Get the scroll height before adding content.
        var scrollTop = $output.offset().top + $output.height();
        // Append the data returned into the main content.
        $output.append('<hr/>');
        $output.append(data);
        // Update listeners for new objects.
        updateListeners();
        // Scroll to the top of the new data.
        $('html, body').animate({scrollTop: scrollTop});
      },
      error: function(jqXHR, textStatus, errorThrown) {
        console.log(textStatus);
        console.log(errorThrown);
      }
    });

    // Reselect the text field.
    $command.select();

    e.preventDefault();
    // e.unbind();
  });

  function updateListeners() {
    $('input[type="button"][is-confirm]').off('.confirm')
    .on('click.confirm', onConfirmClick)
    .each(function (index, element) {
      // Skip if we've already added the confirm dialogue box.
      if ($(element).hasClass('hasConfirm')) return;

      var confirmID = $(this).attr('confirm-id');
      var $container = $('#' + confirmID);
      if ($container.length <= 0) return;

      $(element).easyconfirm({locale: {
        title: $container.find('.alert-title').text(),
        text: $container.find('.alert-text').text(),
        button: [$container.find('.alert-dismiss').val(), $container.find('.alert-ok').val()],
        closeText: 'DO NOT USE THIS'
      }});

      // Add class so we don't add this more than once.
      $(element).addClass('hasConfirm');
    });
  }

  function onConfirmClick (event) {
    // Send off an ajax call to the game with the response.
    alert('TODO: Send ajax call back to game.');
  }

});