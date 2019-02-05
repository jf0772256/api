
function callAPI(url, data, contentType, method, success, error, completed) {
  url = (isUndefined(url) || isNull(url) || url == "") ? false : url;
  if (isBoolean(url)) return;
  method = (isUndefined(method) || isNull(method) || method == "") ? 'GET' : method.toUpperCase();
  success = (!isFunction(success)) ? function(){} : success;
  error = (!isFunction(error)) ? function(){} : error;
  completed = (!isFunction(completed)) ? function(){} : completed;
  if (window.jQuery == null) {
    return;
  }
  $.ajax({
    url: url,
    cache: false,
    method: method,
    success: success,
    error: error,
    completed: completed
  });
}
