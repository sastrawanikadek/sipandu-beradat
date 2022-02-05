const startLoading = () => {
  $("#loading").fadeIn();
  $("#app").css("overflow-y", "hidden")
}

const stopLoading = () => {
  $("#loading").fadeOut();
  $("#app").css("overflow-y", "auto")
}