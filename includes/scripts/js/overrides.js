/** Overrides.js was written by Jesse Fender to allow prototyping js functionality.*/

Date.prototype.toTimeStampString = function() {
    return this.getFullYear() + '-' + (this.getMonth() < 9 ? '0' + (this.getMonth() + 1).toString() : (this.getMonth() + 1).toString()) + '-' + this.getDate() + ' ' + (this.getHours() > 9 ? this.getHours() : "0" + this.getHours()) + ':' + (this.getMinutes() > 9 ? this.getMinutes() : "0" + this.getMinutes()) + ':' + (this.getSeconds() > 9 ? this.getSeconds() : "0" + this.getSeconds());
};

String.prototype.regexIndexOf = function(regex, startpos) {
    var indexOf = this.substring(startpos || 0).search(regex);
    return (indexOf >= 0) ? (indexOf + (startpos || 0)) : indexOf;
}

String.prototype.regexLastIndexOf = function(regex, startpos) {
    regex = (regex.global) ? regex : new RegExp(regex.source, "g" + (regex.ignoreCase ? "i" : "") + (regex.multiLine ? "m" : ""));
    if (typeof(startpos) == "undefined") {
        startpos = this.length;
    } else if (startpos < 0) {
        startpos = 0;
    }
    var stringToWorkWith = this.substring(0, startpos + 1);
    var lastIndexOf = -1;
    var nextStop = 0;
    while ((result = regex.exec(stringToWorkWith)) != null) {
        lastIndexOf = result.index;
        regex.lastIndex = ++nextStop;
    }
    return lastIndexOf;
}

String.prototype.toUpperCaseFirstWord = function() {
    return this.substring(0, 1).toUpperCase() + this.substring(1);
}

/**
 * @param {string[]} tokens Must be an array!
 */
String.prototype.multiSplit = function(tokens) {
    if (!Array.isArray(tokens)) {
        console.error("Invalid token array value! Must be an array!");
        return undefined;
    }
    var str = this;
    var tempChar = tokens[0]; // We can use the first token as a temporary join character
    for (var i = 1; i < tokens.length; i++) {
        str = str.split(tokens[i]).join(tempChar);
    }
    str = str.split(tempChar);
    return str;
}

Number.prototype.formatMoney = function(c, d, t) {
    var n = this,
        c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))),
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

Array.prototype.removeItemByValue = function() {
    var what, a = arguments,
        L = a.length,
        ax;
    while (L >= 1 && this.length) {
        what = a[--L];
        while ((ax = this.indexOf(what)) !== -1) {
            this.splice(ax, 1);
        }
    }
    return this;
};

Array.prototype.removeDuplicates = function() {
    var seen = {};
    var ret_arr = [];
    for (var i = 0; i < this.length; i++) {
        if (!(this[i] in seen)) {
            ret_arr.push(this[i]);
            seen[this[i]] = true;
        }
    }
    return ret_arr;
};

Array.prototype.toLowerCaseArray = function() {
    var union = [];
    for (var i = 0; i < this.length; i++) {
        var ele = this[i].toLowerCase();
        union.push(ele);
    }
    return union;
}

Array.prototype.toUpperCaseArray = function() {
    var confederate = [];
    for (var i = 0; i < this.length; i++) {
        var ele = this[i].toUpperCase();
        confederate.push(ele);
    }
    return confederate;
}

Array.prototype.stringComparer = function(a, b) {
    a = a.toLowerCase();
    b = b.toLowerCase();
    if (a < b) {
        return -1;
    }
    if (a > b) {
        return 1;
    }
    return 0;
};

Array.prototype.stringRevComparer = function(a, b) {
    a = a.toLowerCase();
    b = b.toLowerCase();
    if (a > b) {
        return -1;
    }
    if (a < b) {
        return 1;
    }
    return 0;
};

Array.prototype.numericComparer = function(a, b) {
    return a - b;
}

Array.prototype.numericRevComparer = function(a, b) {
    return b - a;
}