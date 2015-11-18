module.exports = {

    addDays: function(d, days) {
        var newDate = this.clone(d);
        newDate.setDate(d.getDate() + days);
        return newDate;
    },

    addMonths: function(d, months) {
        var newDate = this.clone(d);
        newDate.setMonth(d.getMonth() + months);
        return newDate;
    },

    clone: function(d) {
        return new Date(d.getTime());
    },

    getDaysInMonth: function(d) {
        var resultDate = this.getFirstDayOfMonth(d);

        resultDate.setMonth(resultDate.getMonth() + 1);
        resultDate.setDate(resultDate.getDate() - 1);

        return resultDate.getDate();
    },

    getFirstDayOfMonth: function(d) {
        return new Date(d.getFullYear(), d.getMonth(), 1);
    },

    getFullMonth: function(d) {
        var month = d.getMonth();
        switch (month) {
            case 0: return 'January';
            case 1: return 'February';
            case 2: return 'March';
            case 3: return 'April';
            case 4: return 'May';
            case 5: return 'June';
            case 6: return 'July';
            case 7: return 'August';
            case 8: return 'September';
            case 9: return 'October';
            case 10: return 'November';
            case 11: return 'December';
        }
    },

    getShortMonth: function(d) {
        var month = d.getMonth();
        switch (month) {
            case 0: return 'Jan';
            case 1: return 'Feb';
            case 2: return 'Mar';
            case 3: return 'Apr';
            case 4: return 'May';
            case 5: return 'Jun';
            case 6: return 'Jul';
            case 7: return 'Aug';
            case 8: return 'Sep';
            case 9: return 'Oct';
            case 10: return 'Nov';
            case 11: return 'Dec';
        }
    },

    getDayOfWeek: function(d) {
        var dow = d.getDay();
        switch (dow) {
            case 0: return 'Sunday';
            case 1: return 'Monday';
            case 2: return 'Tuesday';
            case 3: return 'Wednesday';
            case 4: return 'Thursday';
            case 5: return 'Friday';
            case 6: return 'Saturday';
        }
    },

    getWeekArray: function(d) {
        var dayArray = [];
        var daysInMonth = this.getDaysInMonth(d);
        var daysInWeek;
        var emptyDays;
        var firstDayOfWeek;
        var week;
        var weekArray = [];

        for (var i = 1; i <= daysInMonth; i++) {
            dayArray.push(new Date(d.getFullYear(), d.getMonth(), i));
        };

        while (dayArray.length) {
            firstDayOfWeek = dayArray[0].getDay();
            daysInWeek = 7 - firstDayOfWeek;
            emptyDays = 7 - daysInWeek;
            week = dayArray.splice(0, daysInWeek);

            for (var i = 0; i < emptyDays; i++) {
                week.unshift(null);
            };

            weekArray.push(week);
        }

        return weekArray;
    },

    format: function(date, format) {
        var day = date.getDate(),
            month = date.getMonth() + 1,
            year = date.getFullYear(),
            hours = date.getHours(),
            minutes = date.getMinutes(),
            seconds = date.getSeconds();

        if (!format) {
            format = "MM/dd/yyyy";
        }

        format = format.replace("MM", month.toString().replace(/^(\d)$/, '0$1'));

        if (format.indexOf("yyyy") > -1) {
            format = format.replace("yyyy", year.toString());
        } else if (format.indexOf("yy") > -1) {
            format = format.replace("yy", year.toString().substr(2, 2));
        }

        format = format.replace("dd", day.toString().replace(/^(\d)$/, '0$1'));

        if (format.indexOf("t") > -1) {
            if (hours > 11) {
                format = format.replace("t", "pm");
            } else {
                format = format.replace("t", "am");
            }
        }

        if (format.indexOf("HH") > -1) {
            format = format.replace("HH", hours.toString().replace(/^(\d)$/, '0$1'));
        }

        if (format.indexOf("hh") > -1) {
            if (hours > 12) {
                hours -= 12;
            }

            if (hours === 0) {
                hours = 12;
            }
            format = format.replace("hh", hours.toString().replace(/^(\d)$/, '0$1'));
        }

        if (format.indexOf("mm") > -1) {
            format = format.replace("mm", minutes.toString().replace(/^(\d)$/, '0$1'));
        }

        if (format.indexOf("ss") > -1) {
            format = format.replace("ss", seconds.toString().replace(/^(\d)$/, '0$1'));
        }

        return format;
    },

    isEqualDate: function(d1, d2) {
        return d1 && d2 &&
            (d1.getFullYear() === d2.getFullYear()) &&
            (d1.getMonth() === d2.getMonth()) &&
            (d1.getDate() === d2.getDate());
    },

    monthDiff: function(d1, d2) {
        var m;
        m = (d1.getFullYear() - d2.getFullYear()) * 12;
        m += d1.getMonth();
        m -= d2.getMonth();
        return m;
    },

    /**
     * Make sure a string is a validate date
     *
     * @return {bool} true if the string can be converted to a Date
     */
    validateDate: function(dateString) {

        var invalid = isNaN(Date.parse(dateString));

        return (invalid === true) ? false : true;
    }

}