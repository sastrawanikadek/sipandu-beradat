const startLoading = () => {
  $("#loading").fadeIn(300);
  $("#app").css("overflow-y", "hidden")
}

const stopLoading = () => {
  $("#loading").fadeOut(300);
  $("#app").css("overflow-y", "auto")
}