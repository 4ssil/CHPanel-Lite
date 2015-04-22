/**
 * @package     CHPanel
 * @subpackage  com_chpanel
 * @copyright   Copyright (C) 2013 - 2014 CloudHotelier. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.cloudhotelier.com
 * @author      Xavier Pallicer <xpallicer@cloudhotelier.com>
 */

/*
 * CHPanel administrator JS 
 */

(function($, window, document, Joomla) {

    // CHPanel scope
    var CHPanel = {
        // init script
        init: function() {

            this.options = {
                checkfields: 'Errors found. Please check fields and try again',
                datepicker_format: 'dd/mm/yyyy',
                datepicker_weekstart: 1
            };
            this.options = $.extend(this.options, window.chpanel_options);

            var self = this;

            // common tasks
            self.common();

            if ($('#chbanner-close').length) {
                self.banner();
            }

            if ($('#chpanel-view-translation').length) {
                self.translation();
            }

            if ($('#chpanel-bookings-apply').length) {
                self.bookings();
            }

            if ($('#chpanel-manage-apply').length) {
                self.manage();
            }
        }

        // hide banner
        , banner: function() {

            $('#chbanner-close').click(function() {
                document.cookie="chpanelbanner=0";
            });

        }

        // bookings view
        , bookings: function() {

            var self = this;

            // date Pickers
            var datepicker_options = {
                language: self.options.datepicker_lang,
                format: self.options.datepicker_format,
                weekStart: self.options.datepicker_weekstart,
                autoclose: true
            };

            // jqcache fields
            var $start = $('#chpanel-bookings-start');
            var $end = $('#chpanel-bookings-end');

            // strat calendar
            $start.datepicker(datepicker_options);

            // end calendar
            $end.datepicker(datepicker_options);

            // clear
            $('#chpanel-bookings-clear').click(function() {
                $start.val('');
                $end.val('');
            });

            // apply
            $('#chpanel-bookings-apply').click(function() {
                $('#adminForm').submit();
            });

        }

        // manage view
        , manage: function() {

            var self = this;

            // date Pickers
            var today = self.formatDate(new Date());
            var datepicker_options = {
                language: self.options.datepicker_lang,
                format: self.options.datepicker_format,
                weekStart: self.options.datepicker_weekstart,
                startDate: today,
                autoclose: true
            };

            var $start = $('#chpanel-manage-start');
            var $end = $('#chpanel-manage-end');

            $start.datepicker(datepicker_options).on('changeDate', function(ev) {
                var start = new Date(ev.date);
                var end = self.formatDate(start);
                $end.val(end);
                $end.datepicker('setStartDate', end);
                $end.trigger('focus');
            });
            $end.datepicker(datepicker_options);

            // apply
            $('#chpanel-manage-apply').click(function() {
                $('#chpanel-manage-task').val('manage.apply');
                $('#chpanel-manage-form').submit();
            });

        }

        // common tasks
        , common: function() {

            // form submit
            if ($('#adminForm').length) {
                Joomla.submitbutton = function(task) {
                    var t = task.split('.');
                    if (t[1] === 'cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
                        Joomla.submitform(task, document.getElementById('adminForm'));
                    }
                };
            }

            // file input
            if ($('#chpanel-image').length) {
                $('#chpanel-image').bootstrapFileInput();
            }
        }


        // translation view
        , translation: function() {

            // copy translation
            $('.copy').click(function() {
                var value = '#' + $(this).attr('rel');
                var original = value + '_original';
                $($(value)).val($(original).val());
            });

            // translate text
            $('.translate').click(function() {

                var $this = $(this);
                $('#translate_id').val($this.attr('rel'));

                var key = $('#translate_key').val()
                        , source = $('#translate_source').val()
                        , target = $('#translate_target').val()
                        , q = $('#' + $this.attr('rel') + '_original').val();

                $.ajax({
                    url: 'https://www.googleapis.com/language/translate/v2?key=' + key + '&source=' + source + '&target=' + target + '&q=' + q,
                    dataType: 'jsonp',
                    success: function(resp) {
                        $('#' + $('#translate_id').val()).val(resp.data.translations[0].translatedText.replace(/&#39;/g, "'"));
                    }
                });
            });

        }

        // Date foramtting
        , formatDate: function(date) {
            return ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear();
        }

    };


    // init on ready
    $(function() {
        CHPanel.init();
    });

}(window.jQuery, window, document, Joomla));




/*
 Bootstrap - File Input
 ======================
 
 This is meant to convert all file input tags into a set of elements that displays consistently in all browsers.
 
 Converts all
 <input type="file">
 into Bootstrap buttons
 <a class="btn">Browse</a>
 
 */
(function($, window, document) {

    $.fn.bootstrapFileInput = function() {

        this.each(function(i, elem) {

            var $elem = $(elem);

            // Maybe some fields don't need to be standardized.
            if (typeof $elem.attr('data-bfi-disabled') != 'undefined') {
                return;
            }

            // Set the word to be displayed on the button
            var buttonWord = 'Browse';

            if (typeof $elem.attr('title') != 'undefined') {
                buttonWord = $elem.attr('title');
            }

            var className = '';

            if (!!$elem.attr('class')) {
                className = ' ' + $elem.attr('class');
            }

            // Now we're going to wrap that input field with a Bootstrap button.
            // The input will actually still be there, it will just be float above and transparent (done with the CSS).
            $elem.wrap('<a class="file-input-wrapper btn btn-default ' + className + '"></a>').parent().prepend(buttonWord);
        })

                // After we have found all of the file inputs let's apply a listener for tracking the mouse movement.
                // This is important because the in order to give the illusion that this is a button in FF we actually need to move the button from the file input under the cursor. Ugh.
                .promise().done(function() {

            // As the cursor moves over our new Bootstrap button we need to adjust the position of the invisible file input Browse button to be under the cursor.
            // This gives us the pointer cursor that FF denies us
            $('.file-input-wrapper').mousemove(function(cursor) {

                var input, wrapper,
                        wrapperX, wrapperY,
                        inputWidth, inputHeight,
                        cursorX, cursorY;

                // This wrapper element (the button surround this file input)
                wrapper = $(this);
                // The invisible file input element
                input = wrapper.find("input");
                // The left-most position of the wrapper
                wrapperX = wrapper.offset().left;
                // The top-most position of the wrapper
                wrapperY = wrapper.offset().top;
                // The with of the browsers input field
                inputWidth = input.width();
                // The height of the browsers input field
                inputHeight = input.height();
                //The position of the cursor in the wrapper
                cursorX = cursor.pageX;
                cursorY = cursor.pageY;

                //The positions we are to move the invisible file input
                // The 20 at the end is an arbitrary number of pixels that we can shift the input such that cursor is not pointing at the end of the Browse button but somewhere nearer the middle
                moveInputX = cursorX - wrapperX - inputWidth + 20;
                // Slides the invisible input Browse button to be positioned middle under the cursor
                moveInputY = cursorY - wrapperY - (inputHeight / 2);

                // Apply the positioning styles to actually move the invisible file input
                input.css({
                    left: moveInputX,
                    top: moveInputY
                });
            });

            $('body').on('change', '.file-input-wrapper input[type=file]', function() {

                var fileName;
                fileName = $(this).val();

                // Remove any previous file names
                $(this).parent().next('.file-input-name').remove();
                if (!!$(this).prop('files') && $(this).prop('files').length > 1) {
                    fileName = $(this)[0].files.length + ' files';
                    //$(this).parent().after('<span class="file-input-name">'+$(this)[0].files.length+' files</span>');
                }
                else {
                    // var fakepath = 'C:\\fakepath\\';
                    // fileName = $(this).val().replace('C:\\fakepath\\','');
                    fileName = fileName.substring(fileName.lastIndexOf('\\') + 1, fileName.length);
                }

                $(this).parent().after('<span class="file-input-name">' + fileName + '</span>');
            });

        });

    };

    // Add the styles before the first stylesheet
    // This ensures they can be easily overridden with developer styles
    var cssHtml = '<style>' +
            '.file-input-wrapper { overflow: hidden; position: relative; cursor: pointer; z-index: 1; }' +
            '.file-input-wrapper input[type=file], .file-input-wrapper input[type=file]:focus, .file-input-wrapper input[type=file]:hover { position: absolute; top: 0; left: 0; cursor: pointer; opacity: 0; filter: alpha(opacity=0); z-index: 99; outline: 0; }' +
            '.file-input-name { margin-left: 8px; }' +
            '</style>';
    $('link[rel=stylesheet]').eq(0).before(cssHtml);

}(window.jQuery, window, document));
