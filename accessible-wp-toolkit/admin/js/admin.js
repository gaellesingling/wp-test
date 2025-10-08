(function ($) {
    function normaliseColor(value) {
        if (!value) {
            return '';
        }

        var color = value.trim();
        if (!color) {
            return '';
        }

        if (color[0] !== '#') {
            color = '#' + color;
        }

        if (color.length === 4) {
            color = '#' + color[1] + color[1] + color[2] + color[2] + color[3] + color[3];
        }

        if (!/^#([0-9A-F]{6})$/i.test(color)) {
            return '';
        }

        return color.toUpperCase();
    }

    function updatePreview($input) {
        var targetId = $input.data('preview-target');
        if (!targetId) {
            return;
        }

        var color = normaliseColor($input.val());
        var $target = $('#' + targetId);
        if (!$target.length) {
            return;
        }

        if (targetId.indexOf('fg') !== -1) {
            if (color) {
                $target.css('color', color);
            }
        } else if (color) {
            $target.css('background-color', color);
            $('#awpt-preview-fg').css('background-color', color);
        }

        if (!color) {
            $input.addClass('awpt-color-input--invalid');
        } else {
            $input.removeClass('awpt-color-input--invalid');
        }
    }

    $(function () {
        $('.awpt-color-input').each(function () {
            updatePreview($(this));
        }).on('input blur', function () {
            updatePreview($(this));
        });
    });
})(jQuery);
