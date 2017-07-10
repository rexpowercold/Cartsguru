/**
 * Carts Guru
 *
 * @author    LINKT IT
 * @copyright Copyright (c) LINKT IT 2017
 * @license   Commercial license
 */
 require([
     'jquery',
     'domReady!'
],function ($, domReady) {
  $(document).ready(function ($) {

    var fields = {
            firstname: {
              name: 'firstname',
              attr: 'name'
            },
            lastname: {
              name: 'lastname',
              attr: 'name'
            },
            telephone: {
              name: 'telephone',
              attr: 'name'
            },
            email: {
              name: 'customer-email',
              attr: 'id'
            },
            country: {
              name: 'country_id',
              attr: 'name'
            }
        };

    var setupTracking = (function () {
      var retries = 10,
      timeout = 1000;

        return function () {
          var emailEl = getElementByName(fields.firstname);
          if (emailEl.size() === 0 && retries !== 0) {
            setTimeout(setupTracking, timeout);
            retries--;
            return;
          }
          for (var item in fields) {
              if (fields.hasOwnProperty(item)) {
                fields[item] = getElementByName(fields[item]);
              }
          }
          console.log(fields);
          if (fields.email && fields.firstname) {
              for (item in fields) {
                  if (fields.hasOwnProperty(item)) {
                      fields[item].on('blur', trackData);
                  }
              }
          }
        }
    })();

    function getElementByName (el) {
      if (Array.isArray(el.name)) {
          for (var i = 0; i < el.name.length; i++) {
              var element = $("[" + el.attr + "='" + el.name[i] + "']");
              if (el) {
                  return element;
              }
          }
      } else {
          return $("[" + el.attr + "='" + el.name + "']");
      }
    }

    function collectData () {
        var data = [];
        for (var item in fields) {
            if (fields.hasOwnProperty(item)) {
                // Only if email is set
                if (item === 'email' && fields[item].val() === '') {
                    return false;
                }
                data.push((encodeURIComponent(item) + "=" + encodeURIComponent(fields[item].val())));
            }
        }
        return data;
    }

    function trackData () {
        var data = collectData(),
        trackingURL = typeof cartsguru_tracking_url !== 'undefined' ? cartsguru_tracking_url : '/cartsguru/frontend/index';
        console.log(data);
        if (data) {
            xhr = new XMLHttpRequest();
            xhr.open('POST', trackingURL, true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.send(data.join("&"));
        }
    }

    setupTracking();
  });
});
