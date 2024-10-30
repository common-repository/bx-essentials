"use strict";

var addEvent = function addEvent(selector, events, callback) {
  var events_list = events.split(' ');
  events_list.map(function (event) {
    document.addEventListener(event, function (e) {
      if (e.target.matches(selector)) {
        callback.call(e.target, e);
      }
    }, {
      capture: true,
      passive: false
    });
  });
};
"use strict";

var bxCustomFormValidation = function bxCustomFormValidation(e) {
  var _field$parentNode$que;

  var field = e.target;

  if (['radio', 'checkbox', 'color', 'range', 'file'].includes(field.type)) {
    return;
  }

  var validity = field.validity;
  field.parentNode.classList.remove('input-invalid');
  (_field$parentNode$que = field.parentNode.querySelector('.input-error')) === null || _field$parentNode$que === void 0 || _field$parentNode$que.remove();
  var customCallbackResponse;

  if (field.dataset.hasOwnProperty('customValidation') && field.dataset.customValidation.length) {
    var customCallbackUser = field.dataset.customValidation.toString();
    var customCallbackFunction = new Function("\"use strict\"; return (".concat(customCallbackUser, ")"))();

    if (typeof customCallbackFunction === 'function') {
      var customCallbackReturn = customCallbackFunction(field.value);

      if (typeof customCallbackReturn !== 'undefined' && customCallbackReturn !== false && customCallbackReturn.length > 0) {
        customCallbackResponse = customCallbackReturn;
      }
    }
  }

  if (validity.valid && typeof customCallbackResponse === 'undefined') {
    return;
  }

  field.parentNode.classList.add('input-invalid');

  if (validity.rangeOverflow) {
    var type = field.type !== 'number' ? 'date' : 'number';
    var max = type === 'date' ? bxFormatInputDate(field.max, field.type) : field.max;
    var message = field.dataset.hasOwnProperty('rangeOverflow') ? field.dataset.rangeOverflow : gearbxValidity[type].rangeOverflow;
    bxSetInputError(field, message.replace('%max', max));
    return;
  }

  if (validity.rangeUnderflow) {
    var _type = field.type !== 'number' ? 'date' : 'number';

    var min = _type === 'date' ? bxFormatInputDate(field.min, field.type) : field.min;

    var _message = field.dataset.hasOwnProperty('rangeUnderflow') ? field.dataset.rangeUnderflow : gearbxValidity[_type].rangeUnderflow;

    bxSetInputError(field, _message.replace('%min', min));
    return;
  }

  if (validity.tooLong) {
    var _message2 = field.dataset.hasOwnProperty('tooLong') ? field.dataset.tooLong : gearbxValidity.tooLong;

    bxSetInputError(field, _message2.replace('%maxLength', field.maxLength));
    return;
  }

  if (validity.tooShort) {
    var _message3 = field.dataset.hasOwnProperty('tooShort') ? field.dataset.tooShort : gearbxValidity.tooShort;

    bxSetInputError(field, _message3.replace('%minLength', field.minLength));
    return;
  }

  if (validity.stepMismatch) {
    var _message4 = field.dataset.hasOwnProperty('stepMismatch') ? field.dataset.stepMismatch : gearbxValidity.stepMismatch;

    bxSetInputError(field, _message4.replace('%step', field.step));
    return;
  }

  if (validity.badInput) {
    var _message5 = field.dataset.hasOwnProperty('badInput') ? field.dataset.badInput : gearbxValidity.badInput;

    bxSetInputError(field, _message5.replace('%type', gearbxValidity.types[field.type]));
    return;
  }

  if (validity.typeMismatch) {
    var _message6 = field.dataset.hasOwnProperty('typeMismatch') ? field.dataset.typeMismatch : gearbxValidity.badInput;

    bxSetInputError(field, _message6.replace('%type', field.type));
    return;
  }

  if (validity.patternMismatch) {
    var _message7 = field.dataset.hasOwnProperty('patternMismatch') ? field.dataset.patternMismatch : gearbxValidity.patternMismatch;

    bxSetInputError(field, _message7);
    return;
  }

  if (validity.valueMissing) {
    var _message8 = field.dataset.hasOwnProperty('valueMissing') ? field.dataset.valueMissing : gearbxValidity.valueMissing;

    bxSetInputError(field, _message8);
    return;
  }

  bxSetInputError(field, customCallbackResponse);
};
"use strict";

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var bxDoCustomRequest = function bxDoCustomRequest(bit) {
  if ('object' !== _typeof(bit === null || bit === void 0 ? void 0 : bit.body)) {
    throw new Error('body is not a object');
  }

  document.body.classList.add('body-loading');
  var classNames = [];

  if (bit.hasOwnProperty('element') && bit.hasOwnProperty('class')) {
    classNames = bit.class.split(' ');
    document.querySelectorAll(bit.element).forEach(function (element) {
      var _element$classList;

      return (_element$classList = element.classList).add.apply(_element$classList, _toConsumableArray(classNames));
    });
  }

  var fetchBody = new FormData();

  for (var _i = 0, _Object$entries = Object.entries(bit.body); _i < _Object$entries.length; _i++) {
    var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
        key = _Object$entries$_i[0],
        value = _Object$entries$_i[1];

    fetchBody.append(key, value);
  }

  return fetch(gearbx.ajaxUrl, {
    method: 'POST',
    mode: 'same-origin',
    referrerPolicy: 'same-origin',
    body: fetchBody
  }).then(function (res) {
    document.body.classList.remove('body-loading');

    if (bit.hasOwnProperty('element') && bit.hasOwnProperty('class')) {
      document.querySelectorAll(bit.element).forEach(function (element) {
        var _element$classList2;

        return (_element$classList2 = element.classList).remove.apply(_element$classList2, _toConsumableArray(classNames));
      });
    }

    return res.json();
  });
};
"use strict";

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

var bxDoWpRequest = function bxDoWpRequest(e) {
  e.preventDefault();
  e.target.classList.add('action-loading');
  document.body.classList.add('body-loading');
  var fetchBody;

  if (['A', 'BUTTON'].includes(e.target.tagName)) {
    e.target.disabled = true;
    var parsedBody = {};

    if (e.target.dataset.hasOwnProperty('body')) {
      parsedBody = bxParseAttrBody(e.target.dataset.body);
    } else {
      parsedBody = e.target.dataset;
    }

    fetchBody = new FormData();

    for (var _i = 0, _Object$entries = Object.entries(parsedBody); _i < _Object$entries.length; _i++) {
      var _Object$entries$_i = _slicedToArray(_Object$entries[_i], 2),
          key = _Object$entries$_i[0],
          value = _Object$entries$_i[1];

      fetchBody.append(key, value);
    }
  } else if ('FORM' === e.target.tagName) {
    e.target.querySelectorAll('fieldset,button').forEach(function (element) {
      return element.disabled = true;
    });
    fetchBody = new FormData(e.target);
  }

  fetchBody.set('action', bxParseAttrBody(e.target.dataset.action, true));
  return fetch(gearbx.ajaxUrl, {
    method: 'POST',
    mode: 'same-origin',
    referrerPolicy: 'same-origin',
    body: fetchBody
  }).then(function (res) {
    if (['A', 'BUTTON'].includes(e.target.tagName)) {
      e.target.disabled = false;
    } else if ('FORM' === e.target.tagName) {
      e.target.querySelectorAll('fieldset,button').forEach(function (element) {
        return element.disabled = false;
      });
    }

    e.target.classList.remove('action-loading');
    document.body.classList.remove('body-loading');
    return res.json();
  });
};
"use strict";

var bxFormatInputDate = function bxFormatInputDate(date) {
  var type = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'datetime-local';
  if ('week' === type) return date;
  var options = {};

  if (['date', 'month', 'datetime-local'].includes(type)) {
    options.month = 'long';
    options.year = 'numeric';
  }

  if (['date', 'datetime-local'].includes(type)) {
    options.day = 'numeric';
  }

  if (['time', 'datetime-local'].includes(type)) {
    options.hour = '2-digit';
    options.minute = '2-digit';
  }

  var dateJS = new Date();

  if ('datetime-local' === type) {
    dateJS = new Date(date);
  } else {
    var timeS;

    switch (type) {
      case 'time':
        timeS = date.split(':');
        dateJS.setHours(timeS[0]);
        dateJS.setMinutes(timeS[1]);
        break;

      case 'month':
        timeS = date.split('-');
        dateJS.setFullYear(timeS[0]);
        dateJS.setMonth(Number(timeS[1]) - 1);
        break;

      case 'date':
        timeS = date.split('-');
        dateJS.setFullYear(timeS[0]);
        dateJS.setMonth(Number(timeS[1]) - 1);
        dateJS.setDate(timeS[2]);
        break;

      default:
        break;
    }
  }

  return new Intl.DateTimeFormat(gearbx.lang, options).format(dateJS);
};
"use strict";

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) arr2[i] = arr[i]; return arr2; }

var bxHandleWpAction = function bxHandleWpAction(bit) {
  var _bit$key$replace, _bit$key;

  var success = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

  if (typeof bit === 'undefined') {
    return;
  }

  if (!bit.hasOwnProperty('action')) {
    throw new Error('no action present');
  }

  var method = bit.action;
  var source = '';
  var key = (_bit$key$replace = bit === null || bit === void 0 || (_bit$key = bit.key) === null || _bit$key === void 0 ? void 0 : _bit$key.replace(/[^a-zA-Z0-9_-]/g, '')) !== null && _bit$key$replace !== void 0 ? _bit$key$replace : '';
  var splitter = bit.action.indexOf('-');

  if (splitter !== -1) {
    method = bit.action.slice(0, splitter);
    source = bit.action.slice(splitter + 1);
  }

  if (['cookie', 'session', 'local'].includes(method)) {
    if (!success) {
      return;
    }

    var contentParsed = JSON.stringify(bit.content);

    if ('cookie' === method) {
      var duration = bit.hasOwnProperty('duration') ? Number(bit.duration) : 60 * 60 * 24 * 7;
      document.cookie = "bx_".concat(key, "=").concat(contentParsed, ";Path=/;SameSite=Lax;Secure;Max-Age=").concat(duration);
    } else if ('session' === method) {
      sessionStorage.setItem("bx_".concat(key), contentParsed);
    } else if ('local' === method) {
      localStorage.setItem("bx_".concat(key), contentParsed);
    }

    return;
  }

  if ('toast' === method) {
    var _duration = bit.hasOwnProperty('duration') ? bit.duration : null;

    showToast(bit.content, success, _duration);
    return;
  }

  if ('scrollTo' === method) {
    document.querySelector(bit.element).scrollIntoView({
      behavior: 'smooth'
    });
    return;
  }

  if ('go' === method) {
    var _duration2 = bit.hasOwnProperty('duration') ? Number(bit.duration) : 5;

    var url = new URL(bit.content).href;
    setTimeout(function () {
      return location.assign(url);
    }, _duration2 * 1000);
    return;
  }

  if ('open' === method) {
    var _duration3 = bit.hasOwnProperty('duration') ? Number(bit.duration) : 5;

    var _url = new URL(bit.content).href;
    setTimeout(function () {
      return window.open(_url);
    }, _duration3 * 1000);
    return;
  }

  if ('reload' === method) {
    var _duration4 = bit.hasOwnProperty('duration') ? Number(bit.duration) : 5;

    setTimeout(function () {
      return document.location.reload(typeof bit.content !== 'undefined');
    }, _duration4 * 1000);
    return;
  }

  if ('trigger' === method) {
    document.querySelector(bit.element).dispatchEvent(new Event(String(bit.content), {
      bubbles: false,
      cancelable: true
    }));
    return;
  }

  var elementsTarget = document.querySelectorAll(bit.element);
  elementsTarget.forEach(function (element) {
    if ('after' === method) {
      method = 'afterend';
    } else if ('before' === method) {
      method = 'beforebegin';
    } else if ('append' === method) {
      method = 'beforeend';
    } else if ('prepend' === method) {
      method = 'afterbegin';
    }

    if (['beforebegin', 'afterbegin', 'afterend', 'beforeend'].includes(method)) {
      element.insertAdjacentHTML(method, bit.content);
    } else if ('text' === method) {
      element.textContent = bit.content;
    } else if ('html' === method) {
      element.innerHTML = bit.content;
    } else if ('show' === method) {
      element.classList.remove('d-none', 'hidden');
    } else if ('hide' === method) {
      element.classList.add('d-none', 'hidden');
    } else if ('removeClass' === method) {
      var _element$classList;

      (_element$classList = element.classList).remove.apply(_element$classList, _toConsumableArray(bit.content.split(' ')));
    } else if ('addClass' === method) {
      var _element$classList2;

      (_element$classList2 = element.classList).add.apply(_element$classList2, _toConsumableArray(bit.content.split(' ')));
    } else if ('setAttribute' === method) {
      element.setAttribute(bit.key, bit.content);
    } else if ('removeAttribute' === method) {
      element.removeAttribute(bit.key);
    } else if ('remove' === method) {
      element.remove();
    } else {
      throw new Error('unknown action');
    }
  });
};
"use strict";

var bxHandleWpRequest = function bxHandleWpRequest(e) {
  if (document.body.classList.contains('body-loading')) {
    return;
  }

  var request;

  if (e !== null && e !== void 0 && e.type && ['submit', 'click', 'touchstart'].includes(e.type)) {
    request = bxDoWpRequest(e);
  } else {
    request = bxDoCustomRequest(e);
  }

  return request.then(bxHandleWpResponse).catch(bxHandleWpResponse);
};
"use strict";

var bxHandleWpResponse = function bxHandleWpResponse(res) {
  if (res.success === false) {
    console.error(res);
  }

  var bits = Array.isArray(res.data) ? res.data : [res.data];
  bits.map(function (bit) {
    return bxHandleWpAction(bit, res.success);
  });
};
"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

var bxParseAttrBody = function bxParseAttrBody(obj) {
  var noConvert = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

  if (obj.charAt(0) === '\\') {
    obj = obj.replace(/\\/g, '');
  }

  if (obj.charAt(0) === '"') {
    obj = obj.slice(1, -1);
  }

  if (noConvert) {
    return obj;
  }

  if (obj.includes(':') && obj.charAt(0) !== '{') {
    obj = '{' + obj + '}';
  }

  obj = new Function('"use strict";return (' + obj + ')')();

  if ('function' === typeof obj) {
    obj = obj();
  }

  if ('object' !== _typeof(obj)) {
    throw new Error('[data-body] is not a object');
  }

  return obj;
};
"use strict";

var bxSetInputError = function bxSetInputError(current, msg) {
  var position = current.closest('form').dataset.validity;
  if (!['before', 'after'].includes(position)) return;
  var positions = {
    before: 'beforebegin',
    after: 'afterend'
  };

  if (!msg) {
    return;
  }

  current.insertAdjacentHTML(positions[position], "<span class=\"input-error\">".concat(msg, "</span>"));
};
"use strict";

var showToast = function showToast(message) {
  var toastType = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'success';
  var duration = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 7;
  var link = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';

  if (typeof toastType === 'boolean') {
    toastType = toastType ? 'success' : 'error';
  }

  var typeClasses = {
    success: 'toast-success',
    error: 'toast-error',
    warning: 'toast-warning',
    info: 'toast-info'
  };
  var list = document.querySelector('ul.toasts-list');

  if (!list) {
    list = document.createElement('ul');
    list.className = 'toasts-list';
    document.body.appendChild(list);
  }

  var toast = document.createElement('li');
  toast.role = 'alert';
  var classes = ['toast', 'toast-invisible', typeClasses[toastType] || 'toast-' + toastType, 'toast-number-' + document.querySelectorAll('.toast').length];
  toast.className = classes.join(' ');
  var text = document.createTextNode(message);

  if (link) {
    var linkElement = document.createElement('a');
    linkElement.href = link;
    linkElement.target = '_blank';
    linkElement.appendChild(text);
    toast.appendChild(linkElement);
  } else {
    toast.appendChild(text);
  }

  list.appendChild(toast);
  setTimeout(function () {
    toast.classList.remove('toast-invisible');
  }, 1);
  setTimeout(function () {
    toast.classList.add('toast-invisible');
  }, duration * 1000);
  setTimeout(function () {
    list.removeChild(toast);

    if (!document.querySelectorAll('.toast').length) {
      document.body.removeChild(list);
    }
  }, duration * 1000 + 501);
};
"use strict";

/**
 * @namespace Gearbox
 */
addEvent('form[data-action]', 'submit', bxHandleWpRequest);
addEvent('a[data-action],button[data-action]', 'click touchstart', bxHandleWpRequest);
addEvent('form[data-validity] input', 'input change', bxCustomFormValidation);