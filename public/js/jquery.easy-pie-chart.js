// Generated by CoffeeScript 1.4.0

/*
Easy pie chart is a jquery plugin to display simple animated pie charts for only one value

Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.

Built on top of the jQuery library (http://jquery.com)

@source: http://github.com/rendro/easy-pie-chart/
@autor: Robert Fleischmann
@version: 1.0.1

Inspired by: http://dribbble.com/shots/631074-Simple-Pie-Charts-II?list=popular&offset=210
Thanks to Philip Thrasher for the jquery plugin boilerplate for coffee script
*/


(function() {

  (function($) {
    $.easyPieChart = function(el, options) {
      var addScaleLine, animateLine, drawLine, easeInOutQuad, renderBackground, renderScale, renderTrack,
        _this = this;
      this.el = el;
      this.$el = $(el);
      this.$el.data("easyPieChart", this);
      this.init = function() {
        var percent;
        _this.options = $.extend({}, $.easyPieChart.defaultOptions, options);
        percent = parseInt(_this.$el.data('percent'), 10);
        _this.percentage = 0;
        _this.canvas = $("<canvas width='" + _this.options.size + "' height='" + _this.options.size + "'></canvas>").get(0);
        _this.$el.append(_this.canvas);
        if (typeof G_vmlCanvasManager !== "undefined" && G_vmlCanvasManager !== null) {
          G_vmlCanvasManager.initElement(_this.canvas);
        }
        _this.ctx = _this.canvas.getContext('2d');
        if (window.devicePixelRatio > 1.5) {
          $(_this.canvas).css({
            width: _this.options.size,
            height: _this.options.size
          });
          _this.canvas.width *= 2;
          _this.canvas.height *= 2;
          _this.ctx.scale(2, 2);
        }
        _this.ctx.translate(_this.options.size / 2, _this.options.size / 2);
        _this.$el.addClass('easyPieChart');
        _this.$el.css({
          width: _this.options.size,
          height: _this.options.size,
          lineHeight: "" + _this.options.size + "px"
        });
        _this.update(percent);
        return _this;
      };
      this.update = function(percent) {
        if (_this.options.animate === false) {
          return drawLine(percent);
        } else {
          return animateLine(_this.percentage, percent);
        }
      };
      renderScale = function() {
        var i, _i, _results;
        _this.ctx.fillStyle = _this.options.scaleColor;
        _this.ctx.lineWidth = 1;
        _results = [];
        for (i = _i = 0; _i <= 24; i = ++_i) {
          _results.push(addScaleLine(i));
        }
        return _results;
      };
      addScaleLine = function(i) {
        var offset;
        offset = i % 6 === 0 ? 0 : _this.options.size * 0.017;
        _this.ctx.save();
        _this.ctx.rotate(i * Math.PI / 12);
        _this.ctx.fillRect(_this.options.size / 2 - offset, 0, -_this.options.size * 0.05 + offset, 1);
        return _this.ctx.restore();
      };
      renderTrack = function() {
        var offset;
        offset = _this.options.size / 2 - _this.options.lineWidth / 2;
        if (_this.options.scaleColor !== false) {
          offset -= _this.options.size * 0.08;
        }
        _this.ctx.beginPath();
        _this.ctx.arc(0, 0, offset, 0, Math.PI * 2, true);
        _this.ctx.closePath();
        _this.ctx.strokeStyle = _this.options.trackColor;
        _this.ctx.lineWidth = _this.options.lineWidth;
        return _this.ctx.stroke();
      };
      renderBackground = function() {
        if (_this.options.scaleColor !== false) {
          renderScale();
        }
        if (_this.options.trackColor !== false) {
          return renderTrack();
        }
      };
      drawLine = function(percent) {
        var offset;
        renderBackground();
        _this.ctx.strokeStyle = $.isFunction(_this.options.barColor) ? _this.options.barColor(percent) : _this.options.barColor;
        _this.ctx.lineCap = _this.options.lineCap;
        _this.ctx.lineWidth = _this.options.lineWidth;
        offset = _this.options.size / 2 - _this.options.lineWidth / 2;
        if (_this.options.scaleColor !== false) {
          offset -= _this.options.size * 0.08;
        }
        _this.ctx.save();
        _this.ctx.rotate(-Math.PI / 2);
        _this.ctx.beginPath();
        _this.ctx.arc(0, 0, offset, 0, Math.PI * 2 * percent / 100, false);
        _this.ctx.stroke();
        return _this.ctx.restore();
      };
      animateLine = function(from, to) {
        var currentStep, fps, steps;
        fps = 30;
        steps = fps * _this.options.animate / 1000;
        currentStep = 0;
        _this.options.onStart.call(_this);
        _this.percentage = to;
        if (_this.animation) {
          clearInterval(_this.animation);
          _this.animation = false;
        }
        return _this.animation = setInterval(function() {
          _this.ctx.clearRect(-_this.options.size / 2, -_this.options.size / 2, _this.options.size, _this.options.size);
          renderBackground.call(_this);
          drawLine.call(_this, [easeInOutQuad(currentStep, from, to - from, steps)]);
          currentStep++;
          if ((currentStep / steps) > 1) {
            clearInterval(_this.animation);
            _this.animation = false;
            return _this.options.onStop.call(_this);
          }
        }, 1000 / fps);
      };
      easeInOutQuad = function(t, b, c, d) {
        var easeIn, easing;
        easeIn = function(t) {
          return Math.pow(t, 2);
        };
        easing = function(t) {
          if (t < 1) {
            return easeIn(t);
          } else {
            return 2 - easeIn((t / 2) * -2 + 2);
          }
        };
        t /= d / 2;
        return c / 2 * easing(t) + b;
      };
      return this.init();
    };
    $.easyPieChart.defaultOptions = {
      barColor: '#ef1e25',
      trackColor: '#f2f2f2',
      scaleColor: '#dfe0e0',
      lineCap: 'round',
      size: 110,
      lineWidth: 3,
      animate: false,
      onStart: $.noop,
      onStop: $.noop
    };
    $.fn.easyPieChart = function(options) {
      return $.each(this, function(i, el) {
        var $el;
        $el = $(el);
        if (!$el.data('easyPieChart')) {
          return $el.data('easyPieChart', new $.easyPieChart(el, options));
        }
      });
    };
    return void 0;
  })(jQuery);

}).call(this);
;
