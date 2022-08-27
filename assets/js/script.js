/*
 ---
 MooTools: the javascript framework

 web build:
 - http://mootools.net/core/76bf47062d6c1983d66ce47ad66aa0e0

 packager build:
 - packager build Core/Core Core/Array Core/String Core/Number Core/Function Core/Object Core/Event Core/Browser Core/Class Core/Class.Extras Core/Slick.Parser Core/Slick.Finder Core/Element Core/Element.Style Core/Element.Event Core/Element.Delegation Core/Element.Dimensions Core/Fx Core/Fx.CSS Core/Fx.Tween Core/Fx.Morph Core/Fx.Transitions Core/Request Core/Request.HTML Core/Request.JSON Core/Cookie Core/JSON Core/DOMReady Core/Swiff

 ...
 */

/*
 ---

 name: Core

 description: The heart of MooTools.

 license: MIT-style license.

 copyright: Copyright (c) 2006-2012 [Valerio Proietti](http://mad4milk.net/).

 authors: The MooTools production team (http://mootools.net/developers/)

 inspiration:
 - Class implementation inspired by [Base.js](http://dean.edwards.name/weblog/2006/03/base/) Copyright (c) 2006 Dean Edwards, [GNU Lesser General Public License](http://opensource.org/licenses/lgpl-license.php)
 - Some functionality inspired by [Prototype.js](http://prototypejs.org) Copyright (c) 2005-2007 Sam Stephenson, [MIT License](http://opensource.org/licenses/mit-license.php)

 provides: [Core, MooTools, Type, typeOf, instanceOf, Native]

 ...
 */

(function () {

    this.MooTools = {
        version: '1.4.5',
        build: 'ab8ea8824dc3b24b6666867a2c4ed58ebb762cf0'
    };

// typeOf, instanceOf

    var typeOf = this.typeOf = function (item) {
        if (item == null) return 'null';
        if (item.$family != null) return item.$family();

        if (item.nodeName) {
            if (item.nodeType == 1) return 'element';
            if (item.nodeType == 3) return (/\S/).test(item.nodeValue) ? 'textnode' : 'whitespace';
        } else if (typeof item.length == 'number') {
            if (item.callee) return 'arguments';
            if ('item' in item) return 'collection';
        }

        return typeof item;
    };

    var instanceOf = this.instanceOf = function (item, object) {
        if (item == null) return false;
        var constructor = item.$constructor || item.constructor;
        while (constructor) {
            if (constructor === object) return true;
            constructor = constructor.parent;
        }
        /*<ltIE8>*/
        if (!item.hasOwnProperty) return false;
        /*</ltIE8>*/
        return item instanceof object;
    };

// Function overloading

    var Function = this.Function;

    var enumerables = true;
    for (var i in {toString: 1}) enumerables = null;
    if (enumerables) enumerables = ['hasOwnProperty', 'valueOf', 'isPrototypeOf', 'propertyIsEnumerable', 'toLocaleString', 'toString', 'constructor'];

    Function.prototype.overloadSetter = function (usePlural) {
        var self = this;
        return function (a, b) {
            if (a == null) return this;
            if (usePlural || typeof a != 'string') {
                for (var k in a) self.call(this, k, a[k]);
                if (enumerables) for (var i = enumerables.length; i--;) {
                    k = enumerables[i];
                    if (a.hasOwnProperty(k)) self.call(this, k, a[k]);
                }
            } else {
                self.call(this, a, b);
            }
            return this;
        };
    };

    Function.prototype.overloadGetter = function (usePlural) {
        var self = this;
        return function (a) {
            var args, result;
            if (typeof a != 'string') args = a;
            else if (arguments.length > 1) args = arguments;
            else if (usePlural) args = [a];
            if (args) {
                result = {};
                for (var i = 0; i < args.length; i++) result[args[i]] = self.call(this, args[i]);
            } else {
                result = self.call(this, a);
            }
            return result;
        };
    };

    Function.prototype.extend = function (key, value) {
        this[key] = value;
    }.overloadSetter();

    Function.prototype.implement = function (key, value) {
        this.prototype[key] = value;
    }.overloadSetter();

// From

    var slice = Array.prototype.slice;

    Function.from = function (item) {
        return (typeOf(item) == 'function') ? item : function () {
            return item;
        };
    };

    Array.from = function (item) {
        if (item == null) return [];
        return (Type.isEnumerable(item) && typeof item != 'string') ? (typeOf(item) == 'array') ? item : slice.call(item) : [item];
    };

    Number.from = function (item) {
        var number = parseFloat(item);
        return isFinite(number) ? number : null;
    };

    String.from = function (item) {
        return item + '';
    };

// hide, protect

    Function.implement({

        hide: function () {
            this.$hidden = true;
            return this;
        },

        protect: function () {
            this.$protected = true;
            return this;
        }

    });

// Type

    var Type = this.Type = function (name, object) {
        if (name) {
            var lower = name.toLowerCase();
            var typeCheck = function (item) {
                return (typeOf(item) == lower);
            };

            Type['is' + name] = typeCheck;
            if (object != null) {
                object.prototype.$family = (function () {
                    return lower;
                }).hide();
                //<1.2compat>
                object.type = typeCheck;
                //</1.2compat>
            }
        }

        if (object == null) return null;

        object.extend(this);
        object.$constructor = Type;
        object.prototype.$constructor = object;

        return object;
    };

    var toString = Object.prototype.toString;

    Type.isEnumerable = function (item) {
        return (item != null && typeof item.length == 'number' && toString.call(item) != '[object Function]' );
    };

    var hooks = {};

    var hooksOf = function (object) {
        var type = typeOf(object.prototype);
        return hooks[type] || (hooks[type] = []);
    };

    var implement = function (name, method) {
        if (method && method.$hidden) return;

        var hooks = hooksOf(this);

        for (var i = 0; i < hooks.length; i++) {
            var hook = hooks[i];
            if (typeOf(hook) == 'type') implement.call(hook, name, method);
            else hook.call(this, name, method);
        }

        var previous = this.prototype[name];
        if (previous == null || !previous.$protected) this.prototype[name] = method;

        if (this[name] == null && typeOf(method) == 'function') extend.call(this, name, function (item) {
            return method.apply(item, slice.call(arguments, 1));
        });
    };

    var extend = function (name, method) {
        if (method && method.$hidden) return;
        var previous = this[name];
        if (previous == null || !previous.$protected) this[name] = method;
    };

    Type.implement({

        implement: implement.overloadSetter(),

        extend: extend.overloadSetter(),

        alias: function (name, existing) {
            implement.call(this, name, this.prototype[existing]);
        }.overloadSetter(),

        mirror: function (hook) {
            hooksOf(this).push(hook);
            return this;
        }

    });

    new Type('Type', Type);

// Default Types

    var force = function (name, object, methods) {
        var isType = (object != Object),
            prototype = object.prototype;

        if (isType) object = new Type(name, object);

        for (var i = 0, l = methods.length; i < l; i++) {
            var key = methods[i],
                generic = object[key],
                proto = prototype[key];

            if (generic) generic.protect();
            if (isType && proto) object.implement(key, proto.protect());
        }

        if (isType) {
            var methodsEnumerable = prototype.propertyIsEnumerable(methods[0]);
            object.forEachMethod = function (fn) {
                if (!methodsEnumerable) for (var i = 0, l = methods.length; i < l; i++) {
                    fn.call(prototype, prototype[methods[i]], methods[i]);
                }
                for (var key in prototype) fn.call(prototype, prototype[key], key)
            };
        }

        return force;
    };

    force('String', String, [
        'charAt', 'charCodeAt', 'concat', 'indexOf', 'lastIndexOf', 'match', 'quote', 'replace', 'search',
        'slice', 'split', 'substr', 'substring', 'trim', 'toLowerCase', 'toUpperCase'
    ])('Array', Array, [
            'pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift', 'concat', 'join', 'slice',
            'indexOf', 'lastIndexOf', 'filter', 'forEach', 'every', 'map', 'some', 'reduce', 'reduceRight'
        ])('Number', Number, [
            'toExponential', 'toFixed', 'toLocaleString', 'toPrecision'
        ])('Function', Function, [
            'apply', 'call', 'bind'
        ])('RegExp', RegExp, [
            'exec', 'test'
        ])('Object', Object, [
            'create', 'defineProperty', 'defineProperties', 'keys',
            'getPrototypeOf', 'getOwnPropertyDescriptor', 'getOwnPropertyNames',
            'preventExtensions', 'isExtensible', 'seal', 'isSealed', 'freeze', 'isFrozen'
        ])('Date', Date, ['now']);

    Object.extend = extend.overloadSetter();

    Date.extend('now', function () {
        return +(new Date);
    });

    new Type('Boolean', Boolean);

// fixes NaN returning as Number

    Number.prototype.$family = function () {
        return isFinite(this) ? 'number' : 'null';
    }.hide();

// Number.random

    Number.extend('random', function (min, max) {
        return Math.floor(Math.random() * (max - min + 1) + min);
    });

// forEach, each

    var hasOwnProperty = Object.prototype.hasOwnProperty;
    Object.extend('forEach', function (object, fn, bind) {
        for (var key in object) {
            if (hasOwnProperty.call(object, key)) fn.call(bind, object[key], key, object);
        }
    });

    Object.each = Object.forEach;

    Array.implement({

        forEach: function (fn, bind) {
            for (var i = 0, l = this.length; i < l; i++) {
                if (i in this) fn.call(bind, this[i], i, this);
            }
        },

        each: function (fn, bind) {
            Array.forEach(this, fn, bind);
            return this;
        }

    });

// Array & Object cloning, Object merging and appending

    var cloneOf = function (item) {
        switch (typeOf(item)) {
            case 'array':
                return item.clone();
            case 'object':
                return Object.clone(item);
            default:
                return item;
        }
    };

    Array.implement('clone', function () {
        var i = this.length, clone = new Array(i);
        while (i--) clone[i] = cloneOf(this[i]);
        return clone;
    });

    var mergeOne = function (source, key, current) {
        switch (typeOf(current)) {
            case 'object':
                if (typeOf(source[key]) == 'object') Object.merge(source[key], current);
                else source[key] = Object.clone(current);
                break;
            case 'array':
                source[key] = current.clone();
                break;
            default:
                source[key] = current;
        }
        return source;
    };

    Object.extend({

        merge: function (source, k, v) {
            if (typeOf(k) == 'string') return mergeOne(source, k, v);
            for (var i = 1, l = arguments.length; i < l; i++) {
                var object = arguments[i];
                for (var key in object) mergeOne(source, key, object[key]);
            }
            return source;
        },

        clone: function (object) {
            var clone = {};
            for (var key in object) clone[key] = cloneOf(object[key]);
            return clone;
        },

        append: function (original) {
            for (var i = 1, l = arguments.length; i < l; i++) {
                var extended = arguments[i] || {};
                for (var key in extended) original[key] = extended[key];
            }
            return original;
        }

    });

// Object-less types

    ['Object', 'WhiteSpace', 'TextNode', 'Collection', 'Arguments'].each(function (name) {
        new Type(name);
    });

// Unique ID

    var UID = Date.now();

    String.extend('uniqueID', function () {
        return (UID++).toString(36);
    });

//<1.2compat>

    var Hash = this.Hash = new Type('Hash', function (object) {
        if (typeOf(object) == 'hash') object = Object.clone(object.getClean());
        for (var key in object) this[key] = object[key];
        return this;
    });

    Hash.implement({

        forEach: function (fn, bind) {
            Object.forEach(this, fn, bind);
        },

        getClean: function () {
            var clean = {};
            for (var key in this) {
                if (this.hasOwnProperty(key)) clean[key] = this[key];
            }
            return clean;
        },

        getLength: function () {
            var length = 0;
            for (var key in this) {
                if (this.hasOwnProperty(key)) length++;
            }
            return length;
        }

    });

    Hash.alias('each', 'forEach');

    Object.type = Type.isObject;

    var Native = this.Native = function (properties) {
        return new Type(properties.name, properties.initialize);
    };

    Native.type = Type.type;

    Native.implement = function (objects, methods) {
        for (var i = 0; i < objects.length; i++) objects[i].implement(methods);
        return Native;
    };

    var arrayType = Array.type;
    Array.type = function (item) {
        return instanceOf(item, Array) || arrayType(item);
    };

    this.$A = function (item) {
        return Array.from(item).slice();
    };

    this.$arguments = function (i) {
        return function () {
            return arguments[i];
        };
    };

    this.$chk = function (obj) {
        return !!(obj || obj === 0);
    };

    this.$clear = function (timer) {
        clearTimeout(timer);
        clearInterval(timer);
        return null;
    };

    this.$defined = function (obj) {
        return (obj != null);
    };

    this.$each = function (iterable, fn, bind) {
        var type = typeOf(iterable);
        ((type == 'arguments' || type == 'collection' || type == 'array' || type == 'elements') ? Array : Object).each(iterable, fn, bind);
    };

    this.$empty = function () {
    };

    this.$extend = function (original, extended) {
        return Object.append(original, extended);
    };

    this.$H = function (object) {
        return new Hash(object);
    };

    this.$merge = function () {
        var args = Array.slice(arguments);
        args.unshift({});
        return Object.merge.apply(null, args);
    };

    this.$lambda = Function.from;
    this.$mixin = Object.merge;
    this.$random = Number.random;
    this.$splat = Array.from;
    this.$time = Date.now;

    this.$type = function (object) {
        var type = typeOf(object);
        if (type == 'elements') return 'array';
        return (type == 'null') ? false : type;
    };

    this.$unlink = function (object) {
        switch (typeOf(object)) {
            case 'object':
                return Object.clone(object);
            case 'array':
                return Array.clone(object);
            case 'hash':
                return new Hash(object);
            default:
                return object;
        }
    };

//</1.2compat>

})();


/*
 ---

 name: Array

 description: Contains Array Prototypes like each, contains, and erase.

 license: MIT-style license.

 requires: Type

 provides: Array

 ...
 */

Array.implement({

    /*<!ES5>*/
    every: function (fn, bind) {
        for (var i = 0, l = this.length >>> 0; i < l; i++) {
            if ((i in this) && !fn.call(bind, this[i], i, this)) return false;
        }
        return true;
    },

    filter: function (fn, bind) {
        var results = [];
        for (var value, i = 0, l = this.length >>> 0; i < l; i++) if (i in this) {
            value = this[i];
            if (fn.call(bind, value, i, this)) results.push(value);
        }
        return results;
    },

    indexOf: function (item, from) {
        var length = this.length >>> 0;
        for (var i = (from < 0) ? Math.max(0, length + from) : from || 0; i < length; i++) {
            if (this[i] === item) return i;
        }
        return -1;
    },

    map: function (fn, bind) {
        var length = this.length >>> 0, results = Array(length);
        for (var i = 0; i < length; i++) {
            if (i in this) results[i] = fn.call(bind, this[i], i, this);
        }
        return results;
    },

    some: function (fn, bind) {
        for (var i = 0, l = this.length >>> 0; i < l; i++) {
            if ((i in this) && fn.call(bind, this[i], i, this)) return true;
        }
        return false;
    },
    /*</!ES5>*/

    clean: function () {
        return this.filter(function (item) {
            return item != null;
        });
    },

    invoke: function (methodName) {
        var args = Array.slice(arguments, 1);
        return this.map(function (item) {
            return item[methodName].apply(item, args);
        });
    },

    associate: function (keys) {
        var obj = {}, length = Math.min(this.length, keys.length);
        for (var i = 0; i < length; i++) obj[keys[i]] = this[i];
        return obj;
    },

    link: function (object) {
        var result = {};
        for (var i = 0, l = this.length; i < l; i++) {
            for (var key in object) {
                if (object[key](this[i])) {
                    result[key] = this[i];
                    delete object[key];
                    break;
                }
            }
        }
        return result;
    },

    contains: function (item, from) {
        return this.indexOf(item, from) != -1;
    },

    append: function (array) {
        this.push.apply(this, array);
        return this;
    },

    getLast: function () {
        return (this.length) ? this[this.length - 1] : null;
    },

    getRandom: function () {
        return (this.length) ? this[Number.random(0, this.length - 1)] : null;
    },

    include: function (item) {
        if (!this.contains(item)) this.push(item);
        return this;
    },

    combine: function (array) {
        for (var i = 0, l = array.length; i < l; i++) this.include(array[i]);
        return this;
    },

    erase: function (item) {
        for (var i = this.length; i--;) {
            if (this[i] === item) this.splice(i, 1);
        }
        return this;
    },

    empty: function () {
        this.length = 0;
        return this;
    },

    flatten: function () {
        var array = [];
        for (var i = 0, l = this.length; i < l; i++) {
            var type = typeOf(this[i]);
            if (type == 'null') continue;
            array = array.concat((type == 'array' || type == 'collection' || type == 'arguments' || instanceOf(this[i], Array)) ? Array.flatten(this[i]) : this[i]);
        }
        return array;
    },

    pick: function () {
        for (var i = 0, l = this.length; i < l; i++) {
            if (this[i] != null) return this[i];
        }
        return null;
    },

    hexToRgb: function (array) {
        if (this.length != 3) return null;
        var rgb = this.map(function (value) {
            if (value.length == 1) value += value;
            return value.toInt(16);
        });
        return (array) ? rgb : 'rgb(' + rgb + ')';
    },

    rgbToHex: function (array) {
        if (this.length < 3) return null;
        if (this.length == 4 && this[3] == 0 && !array) return 'transparent';
        var hex = [];
        for (var i = 0; i < 3; i++) {
            var bit = (this[i] - 0).toString(16);
            hex.push((bit.length == 1) ? '0' + bit : bit);
        }
        return (array) ? hex : '#' + hex.join('');
    }

});

//<1.2compat>

Array.alias('extend', 'append');

var $pick = function () {
    return Array.from(arguments).pick();
};

//</1.2compat>


/*
 ---

 name: String

 description: Contains String Prototypes like camelCase, capitalize, test, and toInt.

 license: MIT-style license.

 requires: Type

 provides: String

 ...
 */

String.implement({

    test: function (regex, params) {
        return ((typeOf(regex) == 'regexp') ? regex : new RegExp('' + regex, params)).test(this);
    },

    contains: function (string, separator) {
        return (separator) ? (separator + this + separator).indexOf(separator + string + separator) > -1 : String(this).indexOf(string) > -1;
    },

    trim: function () {
        return String(this).replace(/^\s+|\s+$/g, '');
    },

    clean: function () {
        return String(this).replace(/\s+/g, ' ').trim();
    },

    camelCase: function () {
        return String(this).replace(/-\D/g, function (match) {
            return match.charAt(1).toUpperCase();
        });
    },

    hyphenate: function () {
        return String(this).replace(/[A-Z]/g, function (match) {
            return ('-' + match.charAt(0).toLowerCase());
        });
    },

    capitalize: function () {
        return String(this).replace(/\b[a-z]/g, function (match) {
            return match.toUpperCase();
        });
    },

    escapeRegExp: function () {
        return String(this).replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1');
    },

    toInt: function (base) {
        return parseInt(this, base || 10);
    },

    toFloat: function () {
        return parseFloat(this);
    },

    hexToRgb: function (array) {
        var hex = String(this).match(/^#?(\w{1,2})(\w{1,2})(\w{1,2})$/);
        return (hex) ? hex.slice(1).hexToRgb(array) : null;
    },

    rgbToHex: function (array) {
        var rgb = String(this).match(/\d{1,3}/g);
        return (rgb) ? rgb.rgbToHex(array) : null;
    },

    substitute: function (object, regexp) {
        return String(this).replace(regexp || (/\\?\{([^{}]+)\}/g), function (match, name) {
            if (match.charAt(0) == '\\') return match.slice(1);
            return (object[name] != null) ? object[name] : '';
        });
    }

});


/*
 ---

 name: Number

 description: Contains Number Prototypes like limit, round, times, and ceil.

 license: MIT-style license.

 requires: Type

 provides: Number

 ...
 */

Number.implement({

    limit: function (min, max) {
        return Math.min(max, Math.max(min, this));
    },

    round: function (precision) {
        precision = Math.pow(10, precision || 0).toFixed(precision < 0 ? -precision : 0);
        return Math.round(this * precision) / precision;
    },

    times: function (fn, bind) {
        for (var i = 0; i < this; i++) fn.call(bind, i, this);
    },

    toFloat: function () {
        return parseFloat(this);
    },

    toInt: function (base) {
        return parseInt(this, base || 10);
    }

});

Number.alias('each', 'times');

(function (math) {
    var methods = {};
    math.each(function (name) {
        if (!Number[name]) methods[name] = function () {
            return Math[name].apply(null, [this].concat(Array.from(arguments)));
        };
    });
    Number.implement(methods);
})(['abs', 'acos', 'asin', 'atan', 'atan2', 'ceil', 'cos', 'exp', 'floor', 'log', 'max', 'min', 'pow', 'sin', 'sqrt', 'tan']);


/*
 ---

 name: Function

 description: Contains Function Prototypes like create, bind, pass, and delay.

 license: MIT-style license.

 requires: Type

 provides: Function

 ...
 */

Function.extend({

    attempt: function () {
        for (var i = 0, l = arguments.length; i < l; i++) {
            try {
                return arguments[i]();
            } catch (e) {
            }
        }
        return null;
    }

});

Function.implement({

    attempt: function (args, bind) {
        try {
            return this.apply(bind, Array.from(args));
        } catch (e) {
        }

        return null;
    },

    /*<!ES5-bind>*/
    bind: function (that) {
        var self = this,
            args = arguments.length > 1 ? Array.slice(arguments, 1) : null,
            F = function () {
            };

        var bound = function () {
            var context = that, length = arguments.length;
            if (this instanceof bound) {
                F.prototype = self.prototype;
                context = new F;
            }
            var result = (!args && !length)
                ? self.call(context)
                : self.apply(context, args && length ? args.concat(Array.slice(arguments)) : args || arguments);
            return context == that ? result : context;
        };
        return bound;
    },
    /*</!ES5-bind>*/

    pass: function (args, bind) {
        var self = this;
        if (args != null) args = Array.from(args);
        return function () {
            return self.apply(bind, args || arguments);
        };
    },

    delay: function (delay, bind, args) {
        return setTimeout(this.pass((args == null ? [] : args), bind), delay);
    },

    periodical: function (periodical, bind, args) {
        return setInterval(this.pass((args == null ? [] : args), bind), periodical);
    }

});

//<1.2compat>

delete Function.prototype.bind;

Function.implement({

    create: function (options) {
        var self = this;
        options = options || {};
        return function (event) {
            var args = options.arguments;
            args = (args != null) ? Array.from(args) : Array.slice(arguments, (options.event) ? 1 : 0);
            if (options.event) args = [event || window.event].extend(args);
            var returns = function () {
                return self.apply(options.bind || null, args);
            };
            if (options.delay) return setTimeout(returns, options.delay);
            if (options.periodical) return setInterval(returns, options.periodical);
            if (options.attempt) return Function.attempt(returns);
            return returns();
        };
    },

    bind: function (bind, args) {
        var self = this;
        if (args != null) args = Array.from(args);
        return function () {
            return self.apply(bind, args || arguments);
        };
    },

    bindWithEvent: function (bind, args) {
        var self = this;
        if (args != null) args = Array.from(args);
        return function (event) {
            return self.apply(bind, (args == null) ? arguments : [event].concat(args));
        };
    },

    run: function (args, bind) {
        return this.apply(bind, Array.from(args));
    }

});

if (Object.create == Function.prototype.create) Object.create = null;

var $try = Function.attempt;

//</1.2compat>


/*
 ---

 name: Object

 description: Object generic methods

 license: MIT-style license.

 requires: Type

 provides: [Object, Hash]

 ...
 */

(function () {

    var hasOwnProperty = Object.prototype.hasOwnProperty;

    Object.extend({

        subset: function (object, keys) {
            var results = {};
            for (var i = 0, l = keys.length; i < l; i++) {
                var k = keys[i];
                if (k in object) results[k] = object[k];
            }
            return results;
        },

        map: function (object, fn, bind) {
            var results = {};
            for (var key in object) {
                if (hasOwnProperty.call(object, key)) results[key] = fn.call(bind, object[key], key, object);
            }
            return results;
        },

        filter: function (object, fn, bind) {
            var results = {};
            for (var key in object) {
                var value = object[key];
                if (hasOwnProperty.call(object, key) && fn.call(bind, value, key, object)) results[key] = value;
            }
            return results;
        },

        every: function (object, fn, bind) {
            for (var key in object) {
                if (hasOwnProperty.call(object, key) && !fn.call(bind, object[key], key)) return false;
            }
            return true;
        },

        some: function (object, fn, bind) {
            for (var key in object) {
                if (hasOwnProperty.call(object, key) && fn.call(bind, object[key], key)) return true;
            }
            return false;
        },

        keys: function (object) {
            var keys = [];
            for (var key in object) {
                if (hasOwnProperty.call(object, key)) keys.push(key);
            }
            return keys;
        },

        values: function (object) {
            var values = [];
            for (var key in object) {
                if (hasOwnProperty.call(object, key)) values.push(object[key]);
            }
            return values;
        },

        getLength: function (object) {
            return Object.keys(object).length;
        },

        keyOf: function (object, value) {
            for (var key in object) {
                if (hasOwnProperty.call(object, key) && object[key] === value) return key;
            }
            return null;
        },

        contains: function (object, value) {
            return Object.keyOf(object, value) != null;
        },

        toQueryString: function (object, base) {
            var queryString = [];

            Object.each(object, function (value, key) {
                if (base) key = base + '[' + key + ']';
                var result;
                switch (typeOf(value)) {
                    case 'object':
                        result = Object.toQueryString(value, key);
                        break;
                    case 'array':
                        var qs = {};
                        value.each(function (val, i) {
                            qs[i] = val;
                        });
                        result = Object.toQueryString(qs, key);
                        break;
                    default:
                        result = key + '=' + encodeURIComponent(value);
                }
                if (value != null) queryString.push(result);
            });

            return queryString.join('&');
        }

    });

})();

//<1.2compat>

Hash.implement({

    has: Object.prototype.hasOwnProperty,

    keyOf: function (value) {
        return Object.keyOf(this, value);
    },

    hasValue: function (value) {
        return Object.contains(this, value);
    },

    extend: function (properties) {
        Hash.each(properties || {}, function (value, key) {
            Hash.set(this, key, value);
        }, this);
        return this;
    },

    combine: function (properties) {
        Hash.each(properties || {}, function (value, key) {
            Hash.include(this, key, value);
        }, this);
        return this;
    },

    erase: function (key) {
        if (this.hasOwnProperty(key)) delete this[key];
        return this;
    },

    get: function (key) {
        return (this.hasOwnProperty(key)) ? this[key] : null;
    },

    set: function (key, value) {
        if (!this[key] || this.hasOwnProperty(key)) this[key] = value;
        return this;
    },

    empty: function () {
        Hash.each(this, function (value, key) {
            delete this[key];
        }, this);
        return this;
    },

    include: function (key, value) {
        if (this[key] == null) this[key] = value;
        return this;
    },

    map: function (fn, bind) {
        return new Hash(Object.map(this, fn, bind));
    },

    filter: function (fn, bind) {
        return new Hash(Object.filter(this, fn, bind));
    },

    every: function (fn, bind) {
        return Object.every(this, fn, bind);
    },

    some: function (fn, bind) {
        return Object.some(this, fn, bind);
    },

    getKeys: function () {
        return Object.keys(this);
    },

    getValues: function () {
        return Object.values(this);
    },

    toQueryString: function (base) {
        return Object.toQueryString(this, base);
    }

});

Hash.extend = Object.append;

Hash.alias({indexOf: 'keyOf', contains: 'hasValue'});

//</1.2compat>


/*
 ---

 name: Browser

 description: The Browser Object. Contains Browser initialization, Window and Document, and the Browser Hash.

 license: MIT-style license.

 requires: [Array, Function, Number, String]

 provides: [Browser, Window, Document]

 ...
 */

(function () {

    var document = this.document;
    var window = document.window = this;

    var ua = navigator.userAgent.toLowerCase(),
        platform = navigator.platform.toLowerCase(),
        UA = ua.match(/(opera|ie|firefox|chrome|version)[\s\/:]([\w\d\.]+)?.*?(safari|version[\s\/:]([\w\d\.]+)|$)/) || [null, 'unknown', 0],
        mode = UA[1] == 'ie' && document.documentMode;

    var Browser = this.Browser = {

        extend: Function.prototype.extend,

        name: (UA[1] == 'version') ? UA[3] : UA[1],

        version: mode || parseFloat((UA[1] == 'opera' && UA[4]) ? UA[4] : UA[2]),

        Platform: {
            name: ua.match(/ip(?:ad|od|hone)/) ? 'ios' : (ua.match(/(?:webos|android)/) || platform.match(/mac|win|linux/) || ['other'])[0]
        },

        Features: {
            xpath: !!(document.evaluate),
            air: !!(window.runtime),
            query: !!(document.querySelector),
            json: !!(window.JSON)
        },

        Plugins: {}

    };

    Browser[Browser.name] = true;
    Browser[Browser.name + parseInt(Browser.version, 10)] = true;
    Browser.Platform[Browser.Platform.name] = true;

// Request

    Browser.Request = (function () {

        var XMLHTTP = function () {
            return new XMLHttpRequest();
        };

        var MSXML2 = function () {
            return new ActiveXObject('MSXML2.XMLHTTP');
        };

        var MSXML = function () {
            return new ActiveXObject('Microsoft.XMLHTTP');
        };

        return Function.attempt(function () {
            XMLHTTP();
            return XMLHTTP;
        }, function () {
            MSXML2();
            return MSXML2;
        }, function () {
            MSXML();
            return MSXML;
        });

    })();

    Browser.Features.xhr = !!(Browser.Request);

// Flash detection

    var version = (Function.attempt(function () {
        return navigator.plugins['Shockwave Flash'].description;
    }, function () {
        return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version');
    }) || '0 r0').match(/\d+/g);

    Browser.Plugins.Flash = {
        version: Number(version[0] || '0.' + version[1]) || 0,
        build: Number(version[2]) || 0
    };

// String scripts

    Browser.exec = function (text) {
        if (!text) return text;
        if (window.execScript) {
            window.execScript(text);
        } else {
            var script = document.createElement('script');
            script.setAttribute('type', 'text/javascript');
            script.text = text;
            document.head.appendChild(script);
            document.head.removeChild(script);
        }
        return text;
    };

    String.implement('stripScripts', function (exec) {
        var scripts = '';
        var text = this.replace(/<script[^>]*>([\s\S]*?)<\/script>/gi, function (all, code) {
            scripts += code + '\n';
            return '';
        });
        if (exec === true) Browser.exec(scripts);
        else if (typeOf(exec) == 'function') exec(scripts, text);
        return text;
    });

// Window, Document

    Browser.extend({
        Document: this.Document,
        Window: this.Window,
        Element: this.Element,
        Event: this.Event
    });

    this.Window = this.$constructor = new Type('Window', function () {
    });

    this.$family = Function.from('window').hide();

    Window.mirror(function (name, method) {
        window[name] = method;
    });

    this.Document = document.$constructor = new Type('Document', function () {
    });

    document.$family = Function.from('document').hide();

    Document.mirror(function (name, method) {
        document[name] = method;
    });

    document.html = document.documentElement;
    if (!document.head) document.head = document.getElementsByTagName('head')[0];

    if (document.execCommand) try {
        document.execCommand("BackgroundImageCache", false, true);
    } catch (e) {
    }

    /*<ltIE9>*/
    if (this.attachEvent && !this.addEventListener) {
        var unloadEvent = function () {
            this.detachEvent('onunload', unloadEvent);
            document.head = document.html = document.window = null;
        };
        this.attachEvent('onunload', unloadEvent);
    }

// IE fails on collections and <select>.options (refers to <select>)
    var arrayFrom = Array.from;
    try {
        arrayFrom(document.html.childNodes);
    } catch (e) {
        Array.from = function (item) {
            if (typeof item != 'string' && Type.isEnumerable(item) && typeOf(item) != 'array') {
                var i = item.length, array = new Array(i);
                while (i--) array[i] = item[i];
                return array;
            }
            return arrayFrom(item);
        };

        var prototype = Array.prototype,
            slice = prototype.slice;
        ['pop', 'push', 'reverse', 'shift', 'sort', 'splice', 'unshift', 'concat', 'join', 'slice'].each(function (name) {
            var method = prototype[name];
            Array[name] = function (item) {
                return method.apply(Array.from(item), slice.call(arguments, 1));
            };
        });
    }
    /*</ltIE9>*/

//<1.2compat>

    if (Browser.Platform.ios) Browser.Platform.ipod = true;

    Browser.Engine = {};

    var setEngine = function (name, version) {
        Browser.Engine.name = name;
        Browser.Engine[name + version] = true;
        Browser.Engine.version = version;
    };

    if (Browser.ie) {
        Browser.Engine.trident = true;

        switch (Browser.version) {
            case 6:
                setEngine('trident', 4);
                break;
            case 7:
                setEngine('trident', 5);
                break;
            case 8:
                setEngine('trident', 6);
        }
    }

    if (Browser.firefox) {
        Browser.Engine.gecko = true;

        if (Browser.version >= 3) setEngine('gecko', 19);
        else setEngine('gecko', 18);
    }

    if (Browser.safari || Browser.chrome) {
        Browser.Engine.webkit = true;

        switch (Browser.version) {
            case 2:
                setEngine('webkit', 419);
                break;
            case 3:
                setEngine('webkit', 420);
                break;
            case 4:
                setEngine('webkit', 525);
        }
    }

    if (Browser.opera) {
        Browser.Engine.presto = true;

        if (Browser.version >= 9.6) setEngine('presto', 960);
        else if (Browser.version >= 9.5) setEngine('presto', 950);
        else setEngine('presto', 925);
    }

    if (Browser.name == 'unknown') {
        switch ((ua.match(/(?:webkit|khtml|gecko)/) || [])[0]) {
            case 'webkit':
            case 'khtml':
                Browser.Engine.webkit = true;
                break;
            case 'gecko':
                Browser.Engine.gecko = true;
        }
    }

    this.$exec = Browser.exec;

//</1.2compat>

})();


/*
 ---

 name: Event

 description: Contains the Event Type, to make the event object cross-browser.

 license: MIT-style license.

 requires: [Window, Document, Array, Function, String, Object]

 provides: Event

 ...
 */

(function () {

    var _keys = {};

    var DOMEvent = this.DOMEvent = new Type('DOMEvent', function (event, win) {
        if (!win) win = window;
        event = event || win.event;
        if (event.$extended) return event;
        this.event = event;
        this.$extended = true;
        this.shift = event.shiftKey;
        this.control = event.ctrlKey;
        this.alt = event.altKey;
        this.meta = event.metaKey;
        var type = this.type = event.type;
        var target = event.target || event.srcElement;
        while (target && target.nodeType == 3) target = target.parentNode;
        this.target = document.id(target);

        if (type.indexOf('key') == 0) {
            var code = this.code = (event.which || event.keyCode);
            this.key = _keys[code]/*<1.3compat>*/ || Object.keyOf(Event.Keys, code)/*</1.3compat>*/;
            if (type == 'keydown') {
                if (code > 111 && code < 124) this.key = 'f' + (code - 111);
                else if (code > 95 && code < 106) this.key = code - 96;
            }
            if (this.key == null) this.key = String.fromCharCode(code).toLowerCase();
        } else if (type == 'click' || type == 'dblclick' || type == 'contextmenu' || type == 'DOMMouseScroll' || type.indexOf('mouse') == 0) {
            var doc = win.document;
            doc = (!doc.compatMode || doc.compatMode == 'CSS1Compat') ? doc.html : doc.body;
            this.page = {
                x: (event.pageX != null) ? event.pageX : event.clientX + doc.scrollLeft,
                y: (event.pageY != null) ? event.pageY : event.clientY + doc.scrollTop
            };
            this.client = {
                x: (event.pageX != null) ? event.pageX - win.pageXOffset : event.clientX,
                y: (event.pageY != null) ? event.pageY - win.pageYOffset : event.clientY
            };
            if (type == 'DOMMouseScroll' || type == 'mousewheel')
                this.wheel = (event.wheelDelta) ? event.wheelDelta / 120 : -(event.detail || 0) / 3;

            this.rightClick = (event.which == 3 || event.button == 2);
            if (type == 'mouseover' || type == 'mouseout') {
                var related = event.relatedTarget || event[(type == 'mouseover' ? 'from' : 'to') + 'Element'];
                while (related && related.nodeType == 3) related = related.parentNode;
                this.relatedTarget = document.id(related);
            }
        } else if (type.indexOf('touch') == 0 || type.indexOf('gesture') == 0) {
            this.rotation = event.rotation;
            this.scale = event.scale;
            this.targetTouches = event.targetTouches;
            this.changedTouches = event.changedTouches;
            var touches = this.touches = event.touches;
            if (touches && touches[0]) {
                var touch = touches[0];
                this.page = {x: touch.pageX, y: touch.pageY};
                this.client = {x: touch.clientX, y: touch.clientY};
            }
        }

        if (!this.client) this.client = {};
        if (!this.page) this.page = {};
    });

    DOMEvent.implement({

        stop: function () {
            return this.preventDefault().stopPropagation();
        },

        stopPropagation: function () {
            if (this.event.stopPropagation) this.event.stopPropagation();
            else this.event.cancelBubble = true;
            return this;
        },

        preventDefault: function () {
            if (this.event.preventDefault) this.event.preventDefault();
            else this.event.returnValue = false;
            return this;
        }

    });

    DOMEvent.defineKey = function (code, key) {
        _keys[code] = key;
        return this;
    };

    DOMEvent.defineKeys = DOMEvent.defineKey.overloadSetter(true);

    DOMEvent.defineKeys({
        '38': 'up', '40': 'down', '37': 'left', '39': 'right',
        '27': 'esc', '32': 'space', '8': 'backspace', '9': 'tab',
        '46': 'delete', '13': 'enter'
    });

})();

/*<1.3compat>*/
var Event = DOMEvent;
Event.Keys = {};
/*</1.3compat>*/

/*<1.2compat>*/

Event.Keys = new Hash(Event.Keys);

/*</1.2compat>*/


/*
 ---

 name: Class

 description: Contains the Class Function for easily creating, extending, and implementing reusable Classes.

 license: MIT-style license.

 requires: [Array, String, Function, Number]

 provides: Class

 ...
 */

(function () {

    var Class = this.Class = new Type('Class', function (params) {
        if (instanceOf(params, Function)) params = {initialize: params};

        var newClass = function () {
            reset(this);
            if (newClass.$prototyping) return this;
            this.$caller = null;
            var value = (this.initialize) ? this.initialize.apply(this, arguments) : this;
            this.$caller = this.caller = null;
            return value;
        }.extend(this).implement(params);

        newClass.$constructor = Class;
        newClass.prototype.$constructor = newClass;
        newClass.prototype.parent = parent;

        return newClass;
    });

    var parent = function () {
        if (!this.$caller) throw new Error('The method "parent" cannot be called.');
        var name = this.$caller.$name,
            parent = this.$caller.$owner.parent,
            previous = (parent) ? parent.prototype[name] : null;
        if (!previous) throw new Error('The method "' + name + '" has no parent.');
        return previous.apply(this, arguments);
    };

    var reset = function (object) {
        for (var key in object) {
            var value = object[key];
            switch (typeOf(value)) {
                case 'object':
                    var F = function () {
                    };
                    F.prototype = value;
                    object[key] = reset(new F);
                    break;
                case 'array':
                    object[key] = value.clone();
                    break;
            }
        }
        return object;
    };

    var wrap = function (self, key, method) {
        if (method.$origin) method = method.$origin;
        var wrapper = function () {
            if (method.$protected && this.$caller == null) throw new Error('The method "' + key + '" cannot be called.');
            var caller = this.caller, current = this.$caller;
            this.caller = current;
            this.$caller = wrapper;
            var result = method.apply(this, arguments);
            this.$caller = current;
            this.caller = caller;
            return result;
        }.extend({$owner: self, $origin: method, $name: key});
        return wrapper;
    };

    var implement = function (key, value, retain) {
        if (Class.Mutators.hasOwnProperty(key)) {
            value = Class.Mutators[key].call(this, value);
            if (value == null) return this;
        }

        if (typeOf(value) == 'function') {
            if (value.$hidden) return this;
            this.prototype[key] = (retain) ? value : wrap(this, key, value);
        } else {
            Object.merge(this.prototype, key, value);
        }

        return this;
    };

    var getInstance = function (klass) {
        klass.$prototyping = true;
        var proto = new klass;
        delete klass.$prototyping;
        return proto;
    };

    Class.implement('implement', implement.overloadSetter());

    Class.Mutators = {

        Extends: function (parent) {
            this.parent = parent;
            this.prototype = getInstance(parent);
        },

        Implements: function (items) {
            Array.from(items).each(function (item) {
                var instance = new item;
                for (var key in instance) implement.call(this, key, instance[key], true);
            }, this);
        }
    };

})();


/*
 ---

 name: Class.Extras

 description: Contains Utility Classes that can be implemented into your own Classes to ease the execution of many common tasks.

 license: MIT-style license.

 requires: Class

 provides: [Class.Extras, Chain, Events, Options]

 ...
 */

(function () {

    this.Chain = new Class({

        $chain: [],

        chain: function () {
            this.$chain.append(Array.flatten(arguments));
            return this;
        },

        callChain: function () {
            return (this.$chain.length) ? this.$chain.shift().apply(this, arguments) : false;
        },

        clearChain: function () {
            this.$chain.empty();
            return this;
        }

    });

    var removeOn = function (string) {
        return string.replace(/^on([A-Z])/, function (full, first) {
            return first.toLowerCase();
        });
    };

    this.Events = new Class({

        $events: {},

        addEvent: function (type, fn, internal) {
            type = removeOn(type);

            /*<1.2compat>*/
            if (fn == $empty) return this;
            /*</1.2compat>*/

            this.$events[type] = (this.$events[type] || []).include(fn);
            if (internal) fn.internal = true;
            return this;
        },

        addEvents: function (events) {
            for (var type in events) this.addEvent(type, events[type]);
            return this;
        },

        fireEvent: function (type, args, delay) {
            type = removeOn(type);
            var events = this.$events[type];
            if (!events) return this;
            args = Array.from(args);
            events.each(function (fn) {
                if (delay) fn.delay(delay, this, args);
                else fn.apply(this, args);
            }, this);
            return this;
        },

        removeEvent: function (type, fn) {
            type = removeOn(type);
            var events = this.$events[type];
            if (events && !fn.internal) {
                var index = events.indexOf(fn);
                if (index != -1) delete events[index];
            }
            return this;
        },

        removeEvents: function (events) {
            var type;
            if (typeOf(events) == 'object') {
                for (type in events) this.removeEvent(type, events[type]);
                return this;
            }
            if (events) events = removeOn(events);
            for (type in this.$events) {
                if (events && events != type) continue;
                var fns = this.$events[type];
                for (var i = fns.length; i--;) if (i in fns) {
                    this.removeEvent(type, fns[i]);
                }
            }
            return this;
        }

    });

    this.Options = new Class({

        setOptions: function () {
            var options = this.options = Object.merge.apply(null, [
                {},
                this.options
            ].append(arguments));
            if (this.addEvent) for (var option in options) {
                if (typeOf(options[option]) != 'function' || !(/^on[A-Z]/).test(option)) continue;
                this.addEvent(option, options[option]);
                delete options[option];
            }
            return this;
        }

    });

})();


/*
 ---
 name: Slick.Parser
 description: Standalone CSS3 Selector parser
 provides: Slick.Parser
 ...
 */

;
(function () {

    var parsed,
        separatorIndex,
        combinatorIndex,
        reversed,
        cache = {},
        reverseCache = {},
        reUnescape = /\\/g;

    var parse = function (expression, isReversed) {
        if (expression == null) return null;
        if (expression.Slick === true) return expression;
        expression = ('' + expression).replace(/^\s+|\s+$/g, '');
        reversed = !!isReversed;
        var currentCache = (reversed) ? reverseCache : cache;
        if (currentCache[expression]) return currentCache[expression];
        parsed = {
            Slick: true,
            expressions: [],
            raw: expression,
            reverse: function () {
                return parse(this.raw, true);
            }
        };
        separatorIndex = -1;
        while (expression != (expression = expression.replace(regexp, parser)));
        parsed.length = parsed.expressions.length;
        return currentCache[parsed.raw] = (reversed) ? reverse(parsed) : parsed;
    };

    var reverseCombinator = function (combinator) {
        if (combinator === '!') return ' ';
        else if (combinator === ' ') return '!';
        else if ((/^!/).test(combinator)) return combinator.replace(/^!/, '');
        else return '!' + combinator;
    };

    var reverse = function (expression) {
        var expressions = expression.expressions;
        for (var i = 0; i < expressions.length; i++) {
            var exp = expressions[i];
            var last = {parts: [], tag: '*', combinator: reverseCombinator(exp[0].combinator)};

            for (var j = 0; j < exp.length; j++) {
                var cexp = exp[j];
                if (!cexp.reverseCombinator) cexp.reverseCombinator = ' ';
                cexp.combinator = cexp.reverseCombinator;
                delete cexp.reverseCombinator;
            }

            exp.reverse().push(last);
        }
        return expression;
    };

    var escapeRegExp = function (string) {// Credit: XRegExp 0.6.1 (c) 2007-2008 Steven Levithan <http://stevenlevithan.com/regex/xregexp/> MIT License
        return string.replace(/[-[\]{}()*+?.\\^$|,#\s]/g, function (match) {
            return '\\' + match;
        });
    };

    var regexp = new RegExp(
        /*
         #!/usr/bin/env ruby
         puts "\t\t" + DATA.read.gsub(/\(\?x\)|\s+#.*$|\s+|\\$|\\n/,'')
         __END__
         "(?x)^(?:\
         \\s* ( , ) \\s*               # Separator          \n\
         | \\s* ( <combinator>+ ) \\s*   # Combinator         \n\
         |      ( \\s+ )                 # CombinatorChildren \n\
         |      ( <unicode>+ | \\* )     # Tag                \n\
         | \\#  ( <unicode>+       )     # ID                 \n\
         | \\.  ( <unicode>+       )     # ClassName          \n\
         |                               # Attribute          \n\
         \\[  \
         \\s* (<unicode1>+)  (?:  \
         \\s* ([*^$!~|]?=)  (?:  \
         \\s* (?:\
         ([\"']?)(.*?)\\9 \
         )\
         )  \
         )?  \\s*  \
         \\](?!\\]) \n\
         |   :+ ( <unicode>+ )(?:\
         \\( (?:\
         (?:([\"'])([^\\12]*)\\12)|((?:\\([^)]+\\)|[^()]*)+)\
         ) \\)\
         )?\
         )"
         */
        "^(?:\\s*(,)\\s*|\\s*(<combinator>+)\\s*|(\\s+)|(<unicode>+|\\*)|\\#(<unicode>+)|\\.(<unicode>+)|\\[\\s*(<unicode1>+)(?:\\s*([*^$!~|]?=)(?:\\s*(?:([\"']?)(.*?)\\9)))?\\s*\\](?!\\])|(:+)(<unicode>+)(?:\\((?:(?:([\"'])([^\\13]*)\\13)|((?:\\([^)]+\\)|[^()]*)+))\\))?)"
            .replace(/<combinator>/, '[' + escapeRegExp(">+~`!@$%^&={}\\;</") + ']')
            .replace(/<unicode>/g, '(?:[\\w\\u00a1-\\uFFFF-]|\\\\[^\\s0-9a-f])')
            .replace(/<unicode1>/g, '(?:[:\\w\\u00a1-\\uFFFF-]|\\\\[^\\s0-9a-f])')
    );

    function parser(rawMatch, separator, combinator, combinatorChildren, tagName, id, className, attributeKey, attributeOperator, attributeQuote, attributeValue, pseudoMarker, pseudoClass, pseudoQuote, pseudoClassQuotedValue, pseudoClassValue) {
        if (separator || separatorIndex === -1) {
            parsed.expressions[++separatorIndex] = [];
            combinatorIndex = -1;
            if (separator) return '';
        }

        if (combinator || combinatorChildren || combinatorIndex === -1) {
            combinator = combinator || ' ';
            var currentSeparator = parsed.expressions[separatorIndex];
            if (reversed && currentSeparator[combinatorIndex])
                currentSeparator[combinatorIndex].reverseCombinator = reverseCombinator(combinator);
            currentSeparator[++combinatorIndex] = {combinator: combinator, tag: '*'};
        }

        var currentParsed = parsed.expressions[separatorIndex][combinatorIndex];

        if (tagName) {
            currentParsed.tag = tagName.replace(reUnescape, '');

        } else if (id) {
            currentParsed.id = id.replace(reUnescape, '');

        } else if (className) {
            className = className.replace(reUnescape, '');

            if (!currentParsed.classList) currentParsed.classList = [];
            if (!currentParsed.classes) currentParsed.classes = [];
            currentParsed.classList.push(className);
            currentParsed.classes.push({
                value: className,
                regexp: new RegExp('(^|\\s)' + escapeRegExp(className) + '(\\s|$)')
            });

        } else if (pseudoClass) {
            pseudoClassValue = pseudoClassValue || pseudoClassQuotedValue;
            pseudoClassValue = pseudoClassValue ? pseudoClassValue.replace(reUnescape, '') : null;

            if (!currentParsed.pseudos) currentParsed.pseudos = [];
            currentParsed.pseudos.push({
                key: pseudoClass.replace(reUnescape, ''),
                value: pseudoClassValue,
                type: pseudoMarker.length == 1 ? 'class' : 'element'
            });

        } else if (attributeKey) {
            attributeKey = attributeKey.replace(reUnescape, '');
            attributeValue = (attributeValue || '').replace(reUnescape, '');

            var test, regexp;

            switch (attributeOperator) {
                case '^=' :
                    regexp = new RegExp('^' + escapeRegExp(attributeValue));
                    break;
                case '$=' :
                    regexp = new RegExp(escapeRegExp(attributeValue) + '$');
                    break;
                case '~=' :
                    regexp = new RegExp('(^|\\s)' + escapeRegExp(attributeValue) + '(\\s|$)');
                    break;
                case '|=' :
                    regexp = new RegExp('^' + escapeRegExp(attributeValue) + '(-|$)');
                    break;
                case  '=' :
                    test = function (value) {
                        return attributeValue == value;
                    };
                    break;
                case '*=' :
                    test = function (value) {
                        return value && value.indexOf(attributeValue) > -1;
                    };
                    break;
                case '!=' :
                    test = function (value) {
                        return attributeValue != value;
                    };
                    break;
                default   :
                    test = function (value) {
                        return !!value;
                    };
            }

            if (attributeValue == '' && (/^[*$^]=$/).test(attributeOperator)) test = function () {
                return false;
            };

            if (!test) test = function (value) {
                return value && regexp.test(value);
            };

            if (!currentParsed.attributes) currentParsed.attributes = [];
            currentParsed.attributes.push({
                key: attributeKey,
                operator: attributeOperator,
                value: attributeValue,
                test: test
            });

        }

        return '';
    };

// Slick NS

    var Slick = (this.Slick || {});

    Slick.parse = function (expression) {
        return parse(expression);
    };

    Slick.escapeRegExp = escapeRegExp;

    if (!this.Slick) this.Slick = Slick;

}).apply(/*<CommonJS>*/(typeof exports != 'undefined') ? exports : /*</CommonJS>*/this);


/*
 ---
 name: Slick.Finder
 description: The new, superfast css selector engine.
 provides: Slick.Finder
 requires: Slick.Parser
 ...
 */

;
(function () {

    var local = {},
        featuresCache = {},
        toString = Object.prototype.toString;

// Feature / Bug detection

    local.isNativeCode = function (fn) {
        return (/\{\s*\[native code\]\s*\}/).test('' + fn);
    };

    local.isXML = function (document) {
        return (!!document.xmlVersion) || (!!document.xml) || (toString.call(document) == '[object XMLDocument]') ||
            (document.nodeType == 9 && document.documentElement.nodeName != 'HTML');
    };

    local.setDocument = function (document) {

        // convert elements / window arguments to document. if document cannot be extrapolated, the function returns.
        var nodeType = document.nodeType;
        if (nodeType == 9); // document
        else if (nodeType) document = document.ownerDocument; // node
        else if (document.navigator) document = document.document; // window
        else return;

        // check if it's the old document

        if (this.document === document) return;
        this.document = document;

        // check if we have done feature detection on this document before

        var root = document.documentElement,
            rootUid = this.getUIDXML(root),
            features = featuresCache[rootUid],
            feature;

        if (features) {
            for (feature in features) {
                this[feature] = features[feature];
            }
            return;
        }

        features = featuresCache[rootUid] = {};

        features.root = root;
        features.isXMLDocument = this.isXML(document);

        features.brokenStarGEBTN
            = features.starSelectsClosedQSA
            = features.idGetsName
            = features.brokenMixedCaseQSA
            = features.brokenGEBCN
            = features.brokenCheckedQSA
            = features.brokenEmptyAttributeQSA
            = features.isHTMLDocument
            = features.nativeMatchesSelector
            = false;

        var starSelectsClosed, starSelectsComments,
            brokenSecondClassNameGEBCN, cachedGetElementsByClassName,
            brokenFormAttributeGetter;

        var selected, id = 'slick_uniqueid';
        var testNode = document.createElement('div');

        var testRoot = document.body || document.getElementsByTagName('body')[0] || root;
        testRoot.appendChild(testNode);

        // on non-HTML documents innerHTML and getElementsById doesnt work properly
        try {
            testNode.innerHTML = '<a id="' + id + '"></a>';
            features.isHTMLDocument = !!document.getElementById(id);
        } catch (e) {
        }
        ;

        if (features.isHTMLDocument) {

            testNode.style.display = 'none';

            // IE returns comment nodes for getElementsByTagName('*') for some documents
            testNode.appendChild(document.createComment(''));
            starSelectsComments = (testNode.getElementsByTagName('*').length > 1);

            // IE returns closed nodes (EG:"</foo>") for getElementsByTagName('*') for some documents
            try {
                testNode.innerHTML = 'foo</foo>';
                selected = testNode.getElementsByTagName('*');
                starSelectsClosed = (selected && !!selected.length && selected[0].nodeName.charAt(0) == '/');
            } catch (e) {
            }
            ;

            features.brokenStarGEBTN = starSelectsComments || starSelectsClosed;

            // IE returns elements with the name instead of just id for getElementsById for some documents
            try {
                testNode.innerHTML = '<a name="' + id + '"></a><b id="' + id + '"></b>';
                features.idGetsName = document.getElementById(id) === testNode.firstChild;
            } catch (e) {
            }
            ;

            if (testNode.getElementsByClassName) {

                // Safari 3.2 getElementsByClassName caches results
                try {
                    testNode.innerHTML = '<a class="f"></a><a class="b"></a>';
                    testNode.getElementsByClassName('b').length;
                    testNode.firstChild.className = 'b';
                    cachedGetElementsByClassName = (testNode.getElementsByClassName('b').length != 2);
                } catch (e) {
                }
                ;

                // Opera 9.6 getElementsByClassName doesnt detects the class if its not the first one
                try {
                    testNode.innerHTML = '<a class="a"></a><a class="f b a"></a>';
                    brokenSecondClassNameGEBCN = (testNode.getElementsByClassName('a').length != 2);
                } catch (e) {
                }
                ;

                features.brokenGEBCN = cachedGetElementsByClassName || brokenSecondClassNameGEBCN;
            }

            if (testNode.querySelectorAll) {
                // IE 8 returns closed nodes (EG:"</foo>") for querySelectorAll('*') for some documents
                try {
                    testNode.innerHTML = 'foo</foo>';
                    selected = testNode.querySelectorAll('*');
                    features.starSelectsClosedQSA = (selected && !!selected.length && selected[0].nodeName.charAt(0) == '/');
                } catch (e) {
                }
                ;

                // Safari 3.2 querySelectorAll doesnt work with mixedcase on quirksmode
                try {
                    testNode.innerHTML = '<a class="MiX"></a>';
                    features.brokenMixedCaseQSA = !testNode.querySelectorAll('.MiX').length;
                } catch (e) {
                }
                ;

                // Webkit and Opera dont return selected options on querySelectorAll
                try {
                    testNode.innerHTML = '<select><option selected="selected">a</option></select>';
                    features.brokenCheckedQSA = (testNode.querySelectorAll(':checked').length == 0);
                } catch (e) {
                }
                ;

                // IE returns incorrect results for attr[*^$]="" selectors on querySelectorAll
                try {
                    testNode.innerHTML = '<a class=""></a>';
                    features.brokenEmptyAttributeQSA = (testNode.querySelectorAll('[class*=""]').length != 0);
                } catch (e) {
                }
                ;

            }

            // IE6-7, if a form has an input of id x, form.getAttribute(x) returns a reference to the input
            try {
                testNode.innerHTML = '<form action="s"><input id="action"/></form>';
                brokenFormAttributeGetter = (testNode.firstChild.getAttribute('action') != 's');
            } catch (e) {
            }
            ;

            // native matchesSelector function

            features.nativeMatchesSelector = root.matchesSelector || /*root.msMatchesSelector ||*/ root.mozMatchesSelector || root.webkitMatchesSelector;
            if (features.nativeMatchesSelector) try {
                // if matchesSelector trows errors on incorrect sintaxes we can use it
                features.nativeMatchesSelector.call(root, ':slick');
                features.nativeMatchesSelector = null;
            } catch (e) {
            }
            ;

        }

        try {
            root.slick_expando = 1;
            delete root.slick_expando;
            features.getUID = this.getUIDHTML;
        } catch (e) {
            features.getUID = this.getUIDXML;
        }

        testRoot.removeChild(testNode);
        testNode = selected = testRoot = null;

        // getAttribute

        features.getAttribute = (features.isHTMLDocument && brokenFormAttributeGetter) ? function (node, name) {
            var method = this.attributeGetters[name];
            if (method) return method.call(node);
            var attributeNode = node.getAttributeNode(name);
            return (attributeNode) ? attributeNode.nodeValue : null;
        } : function (node, name) {
            var method = this.attributeGetters[name];
            return (method) ? method.call(node) : node.getAttribute(name);
        };

        // hasAttribute

        features.hasAttribute = (root && this.isNativeCode(root.hasAttribute)) ? function (node, attribute) {
            return node.hasAttribute(attribute);
        } : function (node, attribute) {
            node = node.getAttributeNode(attribute);
            return !!(node && (node.specified || node.nodeValue));
        };

        // contains
        // FIXME: Add specs: local.contains should be different for xml and html documents?
        var nativeRootContains = root && this.isNativeCode(root.contains),
            nativeDocumentContains = document && this.isNativeCode(document.contains);

        features.contains = (nativeRootContains && nativeDocumentContains) ? function (context, node) {
            return context.contains(node);
        } : (nativeRootContains && !nativeDocumentContains) ? function (context, node) {
            // IE8 does not have .contains on document.
            return context === node || ((context === document) ? document.documentElement : context).contains(node);
        } : (root && root.compareDocumentPosition) ? function (context, node) {
            return context === node || !!(context.compareDocumentPosition(node) & 16);
        } : function (context, node) {
            if (node) do {
                if (node === context) return true;
            } while ((node = node.parentNode));
            return false;
        };

        // document order sorting
        // credits to Sizzle (http://sizzlejs.com/)

        features.documentSorter = (root.compareDocumentPosition) ? function (a, b) {
            if (!a.compareDocumentPosition || !b.compareDocumentPosition) return 0;
            return a.compareDocumentPosition(b) & 4 ? -1 : a === b ? 0 : 1;
        } : ('sourceIndex' in root) ? function (a, b) {
            if (!a.sourceIndex || !b.sourceIndex) return 0;
            return a.sourceIndex - b.sourceIndex;
        } : (document.createRange) ? function (a, b) {
            if (!a.ownerDocument || !b.ownerDocument) return 0;
            var aRange = a.ownerDocument.createRange(), bRange = b.ownerDocument.createRange();
            aRange.setStart(a, 0);
            aRange.setEnd(a, 0);
            bRange.setStart(b, 0);
            bRange.setEnd(b, 0);
            return aRange.compareBoundaryPoints(Range.START_TO_END, bRange);
        } : null;

        root = null;

        for (feature in features) {
            this[feature] = features[feature];
        }
    };

// Main Method

    var reSimpleSelector = /^([#.]?)((?:[\w-]+|\*))$/,
        reEmptyAttribute = /\[.+[*$^]=(?:""|'')?\]/,
        qsaFailExpCache = {};

    local.search = function (context, expression, append, first) {

        var found = this.found = (first) ? null : (append || []);

        if (!context) return found;
        else if (context.navigator) context = context.document; // Convert the node from a window to a document
        else if (!context.nodeType) return found;

        // setup

        var parsed, i,
            uniques = this.uniques = {},
            hasOthers = !!(append && append.length),
            contextIsDocument = (context.nodeType == 9);

        if (this.document !== (contextIsDocument ? context : context.ownerDocument)) this.setDocument(context);

        // avoid duplicating items already in the append array
        if (hasOthers) for (i = found.length; i--;) uniques[this.getUID(found[i])] = true;

        // expression checks

        if (typeof expression == 'string') { // expression is a string

            /*<simple-selectors-override>*/
            var simpleSelector = expression.match(reSimpleSelector);
            simpleSelectors: if (simpleSelector) {

                var symbol = simpleSelector[1],
                    name = simpleSelector[2],
                    node, nodes;

                if (!symbol) {

                    if (name == '*' && this.brokenStarGEBTN) break simpleSelectors;
                    nodes = context.getElementsByTagName(name);
                    if (first) return nodes[0] || null;
                    for (i = 0; node = nodes[i++];) {
                        if (!(hasOthers && uniques[this.getUID(node)])) found.push(node);
                    }

                } else if (symbol == '#') {

                    if (!this.isHTMLDocument || !contextIsDocument) break simpleSelectors;
                    node = context.getElementById(name);
                    if (!node) return found;
                    if (this.idGetsName && node.getAttributeNode('id').nodeValue != name) break simpleSelectors;
                    if (first) return node || null;
                    if (!(hasOthers && uniques[this.getUID(node)])) found.push(node);

                } else if (symbol == '.') {

                    if (!this.isHTMLDocument || ((!context.getElementsByClassName || this.brokenGEBCN) && context.querySelectorAll)) break simpleSelectors;
                    if (context.getElementsByClassName && !this.brokenGEBCN) {
                        nodes = context.getElementsByClassName(name);
                        if (first) return nodes[0] || null;
                        for (i = 0; node = nodes[i++];) {
                            if (!(hasOthers && uniques[this.getUID(node)])) found.push(node);
                        }
                    } else {
                        var matchClass = new RegExp('(^|\\s)' + Slick.escapeRegExp(name) + '(\\s|$)');
                        nodes = context.getElementsByTagName('*');
                        for (i = 0; node = nodes[i++];) {
                            className = node.className;
                            if (!(className && matchClass.test(className))) continue;
                            if (first) return node;
                            if (!(hasOthers && uniques[this.getUID(node)])) found.push(node);
                        }
                    }

                }

                if (hasOthers) this.sort(found);
                return (first) ? null : found;

            }
            /*</simple-selectors-override>*/

            /*<query-selector-override>*/
            querySelector: if (context.querySelectorAll) {

                if (!this.isHTMLDocument
                    || qsaFailExpCache[expression]
                    //TODO: only skip when expression is actually mixed case
                    || this.brokenMixedCaseQSA
                    || (this.brokenCheckedQSA && expression.indexOf(':checked') > -1)
                    || (this.brokenEmptyAttributeQSA && reEmptyAttribute.test(expression))
                    || (!contextIsDocument //Abort when !contextIsDocument and...
                    //  there are multiple expressions in the selector
                    //  since we currently only fix non-document rooted QSA for single expression selectors
                    && expression.indexOf(',') > -1
                    )
                    || Slick.disableQSA
                    ) break querySelector;

                var _expression = expression, _context = context;
                if (!contextIsDocument) {
                    // non-document rooted QSA
                    // credits to Andrew Dupont
                    var currentId = _context.getAttribute('id'), slickid = 'slickid__';
                    _context.setAttribute('id', slickid);
                    _expression = '#' + slickid + ' ' + _expression;
                    context = _context.parentNode;
                }

                try {
                    if (first) return context.querySelector(_expression) || null;
                    else nodes = context.querySelectorAll(_expression);
                } catch (e) {
                    qsaFailExpCache[expression] = 1;
                    break querySelector;
                } finally {
                    if (!contextIsDocument) {
                        if (currentId) _context.setAttribute('id', currentId);
                        else _context.removeAttribute('id');
                        context = _context;
                    }
                }

                if (this.starSelectsClosedQSA) for (i = 0; node = nodes[i++];) {
                    if (node.nodeName > '@' && !(hasOthers && uniques[this.getUID(node)])) found.push(node);
                } else for (i = 0; node = nodes[i++];) {
                    if (!(hasOthers && uniques[this.getUID(node)])) found.push(node);
                }

                if (hasOthers) this.sort(found);
                return found;

            }
            /*</query-selector-override>*/

            parsed = this.Slick.parse(expression);
            if (!parsed.length) return found;
        } else if (expression == null) { // there is no expression
            return found;
        } else if (expression.Slick) { // expression is a parsed Slick object
            parsed = expression;
        } else if (this.contains(context.documentElement || context, expression)) { // expression is a node
            (found) ? found.push(expression) : found = expression;
            return found;
        } else { // other junk
            return found;
        }

        /*<pseudo-selectors>*/
        /*<nth-pseudo-selectors>*/

        // cache elements for the nth selectors

        this.posNTH = {};
        this.posNTHLast = {};
        this.posNTHType = {};
        this.posNTHTypeLast = {};

        /*</nth-pseudo-selectors>*/
        /*</pseudo-selectors>*/

        // if append is null and there is only a single selector with one expression use pushArray, else use pushUID
        this.push = (!hasOthers && (first || (parsed.length == 1 && parsed.expressions[0].length == 1))) ? this.pushArray : this.pushUID;

        if (found == null) found = [];

        // default engine

        var j, m, n;
        var combinator, tag, id, classList, classes, attributes, pseudos;
        var currentItems, currentExpression, currentBit, lastBit, expressions = parsed.expressions;

        search: for (i = 0; (currentExpression = expressions[i]); i++) for (j = 0; (currentBit = currentExpression[j]); j++) {

            combinator = 'combinator:' + currentBit.combinator;
            if (!this[combinator]) continue search;

            tag = (this.isXMLDocument) ? currentBit.tag : currentBit.tag.toUpperCase();
            id = currentBit.id;
            classList = currentBit.classList;
            classes = currentBit.classes;
            attributes = currentBit.attributes;
            pseudos = currentBit.pseudos;
            lastBit = (j === (currentExpression.length - 1));

            this.bitUniques = {};

            if (lastBit) {
                this.uniques = uniques;
                this.found = found;
            } else {
                this.uniques = {};
                this.found = [];
            }

            if (j === 0) {
                this[combinator](context, tag, id, classes, attributes, pseudos, classList);
                if (first && lastBit && found.length) break search;
            } else {
                if (first && lastBit) for (m = 0, n = currentItems.length; m < n; m++) {
                    this[combinator](currentItems[m], tag, id, classes, attributes, pseudos, classList);
                    if (found.length) break search;
                } else for (m = 0, n = currentItems.length; m < n; m++) this[combinator](currentItems[m], tag, id, classes, attributes, pseudos, classList);
            }

            currentItems = this.found;
        }

        // should sort if there are nodes in append and if you pass multiple expressions.
        if (hasOthers || (parsed.expressions.length > 1)) this.sort(found);

        return (first) ? (found[0] || null) : found;
    };

// Utils

    local.uidx = 1;
    local.uidk = 'slick-uniqueid';

    local.getUIDXML = function (node) {
        var uid = node.getAttribute(this.uidk);
        if (!uid) {
            uid = this.uidx++;
            node.setAttribute(this.uidk, uid);
        }
        return uid;
    };

    local.getUIDHTML = function (node) {
        return node.uniqueNumber || (node.uniqueNumber = this.uidx++);
    };

// sort based on the setDocument documentSorter method.

    local.sort = function (results) {
        if (!this.documentSorter) return results;
        results.sort(this.documentSorter);
        return results;
    };

    /*<pseudo-selectors>*/
    /*<nth-pseudo-selectors>*/

    local.cacheNTH = {};

    local.matchNTH = /^([+-]?\d*)?([a-z]+)?([+-]\d+)?$/;

    local.parseNTHArgument = function (argument) {
        var parsed = argument.match(this.matchNTH);
        if (!parsed) return false;
        var special = parsed[2] || false;
        var a = parsed[1] || 1;
        if (a == '-') a = -1;
        var b = +parsed[3] || 0;
        parsed =
            (special == 'n') ? {a: a, b: b} :
                (special == 'odd') ? {a: 2, b: 1} :
                    (special == 'even') ? {a: 2, b: 0} : {a: 0, b: a};

        return (this.cacheNTH[argument] = parsed);
    };

    local.createNTHPseudo = function (child, sibling, positions, ofType) {
        return function (node, argument) {
            var uid = this.getUID(node);
            if (!this[positions][uid]) {
                var parent = node.parentNode;
                if (!parent) return false;
                var el = parent[child], count = 1;
                if (ofType) {
                    var nodeName = node.nodeName;
                    do {
                        if (el.nodeName != nodeName) continue;
                        this[positions][this.getUID(el)] = count++;
                    } while ((el = el[sibling]));
                } else {
                    do {
                        if (el.nodeType != 1) continue;
                        this[positions][this.getUID(el)] = count++;
                    } while ((el = el[sibling]));
                }
            }
            argument = argument || 'n';
            var parsed = this.cacheNTH[argument] || this.parseNTHArgument(argument);
            if (!parsed) return false;
            var a = parsed.a, b = parsed.b, pos = this[positions][uid];
            if (a == 0) return b == pos;
            if (a > 0) {
                if (pos < b) return false;
            } else {
                if (b < pos) return false;
            }
            return ((pos - b) % a) == 0;
        };
    };

    /*</nth-pseudo-selectors>*/
    /*</pseudo-selectors>*/

    local.pushArray = function (node, tag, id, classes, attributes, pseudos) {
        if (this.matchSelector(node, tag, id, classes, attributes, pseudos)) this.found.push(node);
    };

    local.pushUID = function (node, tag, id, classes, attributes, pseudos) {
        var uid = this.getUID(node);
        if (!this.uniques[uid] && this.matchSelector(node, tag, id, classes, attributes, pseudos)) {
            this.uniques[uid] = true;
            this.found.push(node);
        }
    };

    local.matchNode = function (node, selector) {
        if (this.isHTMLDocument && this.nativeMatchesSelector) {
            try {
                return this.nativeMatchesSelector.call(node, selector.replace(/\[([^=]+)=\s*([^'"\]]+?)\s*\]/g, '[$1="$2"]'));
            } catch (matchError) {
            }
        }

        var parsed = this.Slick.parse(selector);
        if (!parsed) return true;

        // simple (single) selectors
        var expressions = parsed.expressions, simpleExpCounter = 0, i;
        for (i = 0; (currentExpression = expressions[i]); i++) {
            if (currentExpression.length == 1) {
                var exp = currentExpression[0];
                if (this.matchSelector(node, (this.isXMLDocument) ? exp.tag : exp.tag.toUpperCase(), exp.id, exp.classes, exp.attributes, exp.pseudos)) return true;
                simpleExpCounter++;
            }
        }

        if (simpleExpCounter == parsed.length) return false;

        var nodes = this.search(this.document, parsed), item;
        for (i = 0; item = nodes[i++];) {
            if (item === node) return true;
        }
        return false;
    };

    local.matchPseudo = function (node, name, argument) {
        var pseudoName = 'pseudo:' + name;
        if (this[pseudoName]) return this[pseudoName](node, argument);
        var attribute = this.getAttribute(node, name);
        return (argument) ? argument == attribute : !!attribute;
    };

    local.matchSelector = function (node, tag, id, classes, attributes, pseudos) {
        if (tag) {
            var nodeName = (this.isXMLDocument) ? node.nodeName : node.nodeName.toUpperCase();
            if (tag == '*') {
                if (nodeName < '@') return false; // Fix for comment nodes and closed nodes
            } else {
                if (nodeName != tag) return false;
            }
        }

        if (id && node.getAttribute('id') != id) return false;

        var i, part, cls;
        if (classes) for (i = classes.length; i--;) {
            cls = this.getAttribute(node, 'class');
            if (!(cls && classes[i].regexp.test(cls))) return false;
        }
        if (attributes) for (i = attributes.length; i--;) {
            part = attributes[i];
            if (part.operator ? !part.test(this.getAttribute(node, part.key)) : !this.hasAttribute(node, part.key)) return false;
        }
        if (pseudos) for (i = pseudos.length; i--;) {
            part = pseudos[i];
            if (!this.matchPseudo(node, part.key, part.value)) return false;
        }
        return true;
    };

    var combinators = {

        ' ': function (node, tag, id, classes, attributes, pseudos, classList) { // all child nodes, any level

            var i, item, children;

            if (this.isHTMLDocument) {
                getById: if (id) {
                    item = this.document.getElementById(id);
                    if ((!item && node.all) || (this.idGetsName && item && item.getAttributeNode('id').nodeValue != id)) {
                        // all[id] returns all the elements with that name or id inside node
                        // if theres just one it will return the element, else it will be a collection
                        children = node.all[id];
                        if (!children) return;
                        if (!children[0]) children = [children];
                        for (i = 0; item = children[i++];) {
                            var idNode = item.getAttributeNode('id');
                            if (idNode && idNode.nodeValue == id) {
                                this.push(item, tag, null, classes, attributes, pseudos);
                                break;
                            }
                        }
                        return;
                    }
                    if (!item) {
                        // if the context is in the dom we return, else we will try GEBTN, breaking the getById label
                        if (this.contains(this.root, node)) return;
                        else break getById;
                    } else if (this.document !== node && !this.contains(node, item)) return;
                    this.push(item, tag, null, classes, attributes, pseudos);
                    return;
                }
                getByClass: if (classes && node.getElementsByClassName && !this.brokenGEBCN) {
                    children = node.getElementsByClassName(classList.join(' '));
                    if (!(children && children.length)) break getByClass;
                    for (i = 0; item = children[i++];) this.push(item, tag, id, null, attributes, pseudos);
                    return;
                }
            }
            getByTag: {
                children = node.getElementsByTagName(tag);
                if (!(children && children.length)) break getByTag;
                if (!this.brokenStarGEBTN) tag = null;
                for (i = 0; item = children[i++];) this.push(item, tag, id, classes, attributes, pseudos);
            }
        },

        '>': function (node, tag, id, classes, attributes, pseudos) { // direct children
            if ((node = node.firstChild)) do {
                if (node.nodeType == 1) this.push(node, tag, id, classes, attributes, pseudos);
            } while ((node = node.nextSibling));
        },

        '+': function (node, tag, id, classes, attributes, pseudos) { // next sibling
            while ((node = node.nextSibling)) if (node.nodeType == 1) {
                this.push(node, tag, id, classes, attributes, pseudos);
                break;
            }
        },

        '^': function (node, tag, id, classes, attributes, pseudos) { // first child
            node = node.firstChild;
            if (node) {
                if (node.nodeType == 1) this.push(node, tag, id, classes, attributes, pseudos);
                else this['combinator:+'](node, tag, id, classes, attributes, pseudos);
            }
        },

        '~': function (node, tag, id, classes, attributes, pseudos) { // next siblings
            while ((node = node.nextSibling)) {
                if (node.nodeType != 1) continue;
                var uid = this.getUID(node);
                if (this.bitUniques[uid]) break;
                this.bitUniques[uid] = true;
                this.push(node, tag, id, classes, attributes, pseudos);
            }
        },

        '++': function (node, tag, id, classes, attributes, pseudos) { // next sibling and previous sibling
            this['combinator:+'](node, tag, id, classes, attributes, pseudos);
            this['combinator:!+'](node, tag, id, classes, attributes, pseudos);
        },

        '~~': function (node, tag, id, classes, attributes, pseudos) { // next siblings and previous siblings
            this['combinator:~'](node, tag, id, classes, attributes, pseudos);
            this['combinator:!~'](node, tag, id, classes, attributes, pseudos);
        },

        '!': function (node, tag, id, classes, attributes, pseudos) { // all parent nodes up to document
            while ((node = node.parentNode)) if (node !== this.document) this.push(node, tag, id, classes, attributes, pseudos);
        },

        '!>': function (node, tag, id, classes, attributes, pseudos) { // direct parent (one level)
            node = node.parentNode;
            if (node !== this.document) this.push(node, tag, id, classes, attributes, pseudos);
        },

        '!+': function (node, tag, id, classes, attributes, pseudos) { // previous sibling
            while ((node = node.previousSibling)) if (node.nodeType == 1) {
                this.push(node, tag, id, classes, attributes, pseudos);
                break;
            }
        },

        '!^': function (node, tag, id, classes, attributes, pseudos) { // last child
            node = node.lastChild;
            if (node) {
                if (node.nodeType == 1) this.push(node, tag, id, classes, attributes, pseudos);
                else this['combinator:!+'](node, tag, id, classes, attributes, pseudos);
            }
        },

        '!~': function (node, tag, id, classes, attributes, pseudos) { // previous siblings
            while ((node = node.previousSibling)) {
                if (node.nodeType != 1) continue;
                var uid = this.getUID(node);
                if (this.bitUniques[uid]) break;
                this.bitUniques[uid] = true;
                this.push(node, tag, id, classes, attributes, pseudos);
            }
        }

    };

    for (var c in combinators) local['combinator:' + c] = combinators[c];

    var pseudos = {

        /*<pseudo-selectors>*/

        'empty': function (node) {
            var child = node.firstChild;
            return !(child && child.nodeType == 1) && !(node.innerText || node.textContent || '').length;
        },

        'not': function (node, expression) {
            return !this.matchNode(node, expression);
        },

        'contains': function (node, text) {
            return (node.innerText || node.textContent || '').indexOf(text) > -1;
        },

        'first-child': function (node) {
            while ((node = node.previousSibling)) if (node.nodeType == 1) return false;
            return true;
        },

        'last-child': function (node) {
            while ((node = node.nextSibling)) if (node.nodeType == 1) return false;
            return true;
        },

        'only-child': function (node) {
            var prev = node;
            while ((prev = prev.previousSibling)) if (prev.nodeType == 1) return false;
            var next = node;
            while ((next = next.nextSibling)) if (next.nodeType == 1) return false;
            return true;
        },

        /*<nth-pseudo-selectors>*/

        'nth-child': local.createNTHPseudo('firstChild', 'nextSibling', 'posNTH'),

        'nth-last-child': local.createNTHPseudo('lastChild', 'previousSibling', 'posNTHLast'),

        'nth-of-type': local.createNTHPseudo('firstChild', 'nextSibling', 'posNTHType', true),

        'nth-last-of-type': local.createNTHPseudo('lastChild', 'previousSibling', 'posNTHTypeLast', true),

        'index': function (node, index) {
            return this['pseudo:nth-child'](node, '' + (index + 1));
        },

        'even': function (node) {
            return this['pseudo:nth-child'](node, '2n');
        },

        'odd': function (node) {
            return this['pseudo:nth-child'](node, '2n+1');
        },

        /*</nth-pseudo-selectors>*/

        /*<of-type-pseudo-selectors>*/

        'first-of-type': function (node) {
            var nodeName = node.nodeName;
            while ((node = node.previousSibling)) if (node.nodeName == nodeName) return false;
            return true;
        },

        'last-of-type': function (node) {
            var nodeName = node.nodeName;
            while ((node = node.nextSibling)) if (node.nodeName == nodeName) return false;
            return true;
        },

        'only-of-type': function (node) {
            var prev = node, nodeName = node.nodeName;
            while ((prev = prev.previousSibling)) if (prev.nodeName == nodeName) return false;
            var next = node;
            while ((next = next.nextSibling)) if (next.nodeName == nodeName) return false;
            return true;
        },

        /*</of-type-pseudo-selectors>*/

        // custom pseudos

        'enabled': function (node) {
            return !node.disabled;
        },

        'disabled': function (node) {
            return node.disabled;
        },

        'checked': function (node) {
            return node.checked || node.selected;
        },

        'focus': function (node) {
            return this.isHTMLDocument && this.document.activeElement === node && (node.href || node.type || this.hasAttribute(node, 'tabindex'));
        },

        'root': function (node) {
            return (node === this.root);
        },

        'selected': function (node) {
            return node.selected;
        }

        /*</pseudo-selectors>*/
    };

    for (var p in pseudos) local['pseudo:' + p] = pseudos[p];

// attributes methods

    var attributeGetters = local.attributeGetters = {

        'for': function () {
            return ('htmlFor' in this) ? this.htmlFor : this.getAttribute('for');
        },

        'href': function () {
            return ('href' in this) ? this.getAttribute('href', 2) : this.getAttribute('href');
        },

        'style': function () {
            return (this.style) ? this.style.cssText : this.getAttribute('style');
        },

        'tabindex': function () {
            var attributeNode = this.getAttributeNode('tabindex');
            return (attributeNode && attributeNode.specified) ? attributeNode.nodeValue : null;
        },

        'type': function () {
            return this.getAttribute('type');
        },

        'maxlength': function () {
            var attributeNode = this.getAttributeNode('maxLength');
            return (attributeNode && attributeNode.specified) ? attributeNode.nodeValue : null;
        }

    };

    attributeGetters.MAXLENGTH = attributeGetters.maxLength = attributeGetters.maxlength;

// Slick

    var Slick = local.Slick = (this.Slick || {});

    Slick.version = '1.1.7';

// Slick finder

    Slick.search = function (context, expression, append) {
        return local.search(context, expression, append);
    };

    Slick.find = function (context, expression) {
        return local.search(context, expression, null, true);
    };

// Slick containment checker

    Slick.contains = function (container, node) {
        local.setDocument(container);
        return local.contains(container, node);
    };

// Slick attribute getter

    Slick.getAttribute = function (node, name) {
        local.setDocument(node);
        return local.getAttribute(node, name);
    };

    Slick.hasAttribute = function (node, name) {
        local.setDocument(node);
        return local.hasAttribute(node, name);
    };

// Slick matcher

    Slick.match = function (node, selector) {
        if (!(node && selector)) return false;
        if (!selector || selector === node) return true;
        local.setDocument(node);
        return local.matchNode(node, selector);
    };

// Slick attribute accessor

    Slick.defineAttributeGetter = function (name, fn) {
        local.attributeGetters[name] = fn;
        return this;
    };

    Slick.lookupAttributeGetter = function (name) {
        return local.attributeGetters[name];
    };

// Slick pseudo accessor

    Slick.definePseudo = function (name, fn) {
        local['pseudo:' + name] = function (node, argument) {
            return fn.call(node, argument);
        };
        return this;
    };

    Slick.lookupPseudo = function (name) {
        var pseudo = local['pseudo:' + name];
        if (pseudo) return function (argument) {
            return pseudo.call(this, argument);
        };
        return null;
    };

// Slick overrides accessor

    Slick.override = function (regexp, fn) {
        local.override(regexp, fn);
        return this;
    };

    Slick.isXML = local.isXML;

    Slick.uidOf = function (node) {
        return local.getUIDHTML(node);
    };

    if (!this.Slick) this.Slick = Slick;

}).apply(/*<CommonJS>*/(typeof exports != 'undefined') ? exports : /*</CommonJS>*/this);


/*
 ---

 name: Element

 description: One of the most important items in MooTools. Contains the dollar function, the dollars function, and an handful of cross-browser, time-saver methods to let you easily work with HTML Elements.

 license: MIT-style license.

 requires: [Window, Document, Array, String, Function, Object, Number, Slick.Parser, Slick.Finder]

 provides: [Element, Elements, $, $$, Iframe, Selectors]

 ...
 */

var Element = function (tag, props) {
    var konstructor = Element.Constructors[tag];
    if (konstructor) return konstructor(props);
    if (typeof tag != 'string') return document.id(tag).set(props);

    if (!props) props = {};

    if (!(/^[\w-]+$/).test(tag)) {
        var parsed = Slick.parse(tag).expressions[0][0];
        tag = (parsed.tag == '*') ? 'div' : parsed.tag;
        if (parsed.id && props.id == null) props.id = parsed.id;

        var attributes = parsed.attributes;
        if (attributes) for (var attr, i = 0, l = attributes.length; i < l; i++) {
            attr = attributes[i];
            if (props[attr.key] != null) continue;

            if (attr.value != null && attr.operator == '=') props[attr.key] = attr.value;
            else if (!attr.value && !attr.operator) props[attr.key] = true;
        }

        if (parsed.classList && props['class'] == null) props['class'] = parsed.classList.join(' ');
    }

    return document.newElement(tag, props);
};


if (Browser.Element) {
    Element.prototype = Browser.Element.prototype;
    // IE8 and IE9 require the wrapping.
    Element.prototype._fireEvent = (function (fireEvent) {
        return function (type, event) {
            return fireEvent.call(this, type, event);
        };
    })(Element.prototype.fireEvent);
}

new Type('Element', Element).mirror(function (name) {
    if (Array.prototype[name]) return;

    var obj = {};
    obj[name] = function () {
        var results = [], args = arguments, elements = true;
        for (var i = 0, l = this.length; i < l; i++) {
            var element = this[i], result = results[i] = element[name].apply(element, args);
            elements = (elements && typeOf(result) == 'element');
        }
        return (elements) ? new Elements(results) : results;
    };

    Elements.implement(obj);
});

if (!Browser.Element) {
    Element.parent = Object;

    Element.Prototype = {
        '$constructor': Element,
        '$family': Function.from('element').hide()
    };

    Element.mirror(function (name, method) {
        Element.Prototype[name] = method;
    });
}

Element.Constructors = {};

//<1.2compat>

Element.Constructors = new Hash;

//</1.2compat>

var IFrame = new Type('IFrame', function () {
    var params = Array.link(arguments, {
        properties: Type.isObject,
        iframe: function (obj) {
            return (obj != null);
        }
    });

    var props = params.properties || {}, iframe;
    if (params.iframe) iframe = document.id(params.iframe);
    var onload = props.onload || function () {
    };
    delete props.onload;
    props.id = props.name = [props.id, props.name, iframe ? (iframe.id || iframe.name) : 'IFrame_' + String.uniqueID()].pick();
    iframe = new Element(iframe || 'iframe', props);

    var onLoad = function () {
        onload.call(iframe.contentWindow);
    };

    if (window.frames[props.id]) onLoad();
    else iframe.addListener('load', onLoad);
    return iframe;
});

var Elements = this.Elements = function (nodes) {
    if (nodes && nodes.length) {
        var uniques = {}, node;
        for (var i = 0; node = nodes[i++];) {
            var uid = Slick.uidOf(node);
            if (!uniques[uid]) {
                uniques[uid] = true;
                this.push(node);
            }
        }
    }
};

Elements.prototype = {length: 0};
Elements.parent = Array;

new Type('Elements', Elements).implement({

    filter: function (filter, bind) {
        if (!filter) return this;
        return new Elements(Array.filter(this, (typeOf(filter) == 'string') ? function (item) {
            return item.match(filter);
        } : filter, bind));
    }.protect(),

    push: function () {
        var length = this.length;
        for (var i = 0, l = arguments.length; i < l; i++) {
            var item = document.id(arguments[i]);
            if (item) this[length++] = item;
        }
        return (this.length = length);
    }.protect(),

    unshift: function () {
        var items = [];
        for (var i = 0, l = arguments.length; i < l; i++) {
            var item = document.id(arguments[i]);
            if (item) items.push(item);
        }
        return Array.prototype.unshift.apply(this, items);
    }.protect(),

    concat: function () {
        var newElements = new Elements(this);
        for (var i = 0, l = arguments.length; i < l; i++) {
            var item = arguments[i];
            if (Type.isEnumerable(item)) newElements.append(item);
            else newElements.push(item);
        }
        return newElements;
    }.protect(),

    append: function (collection) {
        for (var i = 0, l = collection.length; i < l; i++) this.push(collection[i]);
        return this;
    }.protect(),

    empty: function () {
        while (this.length) delete this[--this.length];
        return this;
    }.protect()

});

//<1.2compat>

Elements.alias('extend', 'append');

//</1.2compat>

(function () {

// FF, IE
    var splice = Array.prototype.splice, object = {'0': 0, '1': 1, length: 2};

    splice.call(object, 1, 1);
    if (object[1] == 1) Elements.implement('splice', function () {
        var length = this.length;
        var result = splice.apply(this, arguments);
        while (length >= this.length) delete this[length--];
        return result;
    }.protect());

    Array.forEachMethod(function (method, name) {
        Elements.implement(name, method);
    });

    Array.mirror(Elements);

    /*<ltIE8>*/
    var createElementAcceptsHTML;
    try {
        createElementAcceptsHTML = (document.createElement('<input name=x>').name == 'x');
    } catch (e) {
    }

    var escapeQuotes = function (html) {
        return ('' + html).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
    };
    /*</ltIE8>*/

    Document.implement({

        newElement: function (tag, props) {
            if (props && props.checked != null) props.defaultChecked = props.checked;
            /*<ltIE8>*/// Fix for readonly name and type properties in IE < 8
            if (createElementAcceptsHTML && props) {
                tag = '<' + tag;
                if (props.name) tag += ' name="' + escapeQuotes(props.name) + '"';
                if (props.type) tag += ' type="' + escapeQuotes(props.type) + '"';
                tag += '>';
                delete props.name;
                delete props.type;
            }
            /*</ltIE8>*/
            return this.id(this.createElement(tag)).set(props);
        }

    });

})();

(function () {

    Slick.uidOf(window);
    Slick.uidOf(document);

    Document.implement({

        newTextNode: function (text) {
            return this.createTextNode(text);
        },

        getDocument: function () {
            return this;
        },

        getWindow: function () {
            return this.window;
        },

        id: (function () {

            var types = {

                string: function (id, nocash, doc) {
                    id = Slick.find(doc, '#' + id.replace(/(\W)/g, '\\$1'));
                    return (id) ? types.element(id, nocash) : null;
                },

                element: function (el, nocash) {
                    Slick.uidOf(el);
                    if (!nocash && !el.$family && !(/^(?:object|embed)$/i).test(el.tagName)) {
                        var fireEvent = el.fireEvent;
                        // wrapping needed in IE7, or else crash
                        el._fireEvent = function (type, event) {
                            return fireEvent(type, event);
                        };
                        Object.append(el, Element.Prototype);
                    }
                    return el;
                },

                object: function (obj, nocash, doc) {
                    if (obj.toElement) return types.element(obj.toElement(doc), nocash);
                    return null;
                }

            };

            types.textnode = types.whitespace = types.window = types.document = function (zero) {
                return zero;
            };

            return function (el, nocash, doc) {
                if (el && el.$family && el.uniqueNumber) return el;
                var type = typeOf(el);
                return (types[type]) ? types[type](el, nocash, doc || document) : null;
            };

        })()

    });

    if (window.$ == null) Window.implement('$', function (el, nc) {
        return document.id(el, nc, this.document);
    });

    Window.implement({

        getDocument: function () {
            return this.document;
        },

        getWindow: function () {
            return this;
        }

    });

    [Document, Element].invoke('implement', {

        getElements: function (expression) {
            return Slick.search(this, expression, new Elements);
        },

        getElement: function (expression) {
            return document.id(Slick.find(this, expression));
        }

    });

    var contains = {contains: function (element) {
        return Slick.contains(this, element);
    }};

    if (!document.contains) Document.implement(contains);
    if (!document.createElement('div').contains) Element.implement(contains);

//<1.2compat>

    Element.implement('hasChild', function (element) {
        return this !== element && this.contains(element);
    });

    (function (search, find, match) {

        this.Selectors = {};
        var pseudos = this.Selectors.Pseudo = new Hash();

        var addSlickPseudos = function () {
            for (var name in pseudos) if (pseudos.hasOwnProperty(name)) {
                Slick.definePseudo(name, pseudos[name]);
                delete pseudos[name];
            }
        };

        Slick.search = function (context, expression, append) {
            addSlickPseudos();
            return search.call(this, context, expression, append);
        };

        Slick.find = function (context, expression) {
            addSlickPseudos();
            return find.call(this, context, expression);
        };

        Slick.match = function (node, selector) {
            addSlickPseudos();
            return match.call(this, node, selector);
        };

    })(Slick.search, Slick.find, Slick.match);

//</1.2compat>

// tree walking

    var injectCombinator = function (expression, combinator) {
        if (!expression) return combinator;

        expression = Object.clone(Slick.parse(expression));

        var expressions = expression.expressions;
        for (var i = expressions.length; i--;)
            expressions[i][0].combinator = combinator;

        return expression;
    };

    Object.forEach({
        getNext: '~',
        getPrevious: '!~',
        getParent: '!'
    }, function (combinator, method) {
        Element.implement(method, function (expression) {
            return this.getElement(injectCombinator(expression, combinator));
        });
    });

    Object.forEach({
        getAllNext: '~',
        getAllPrevious: '!~',
        getSiblings: '~~',
        getChildren: '>',
        getParents: '!'
    }, function (combinator, method) {
        Element.implement(method, function (expression) {
            return this.getElements(injectCombinator(expression, combinator));
        });
    });

    Element.implement({

        getFirst: function (expression) {
            return document.id(Slick.search(this, injectCombinator(expression, '>'))[0]);
        },

        getLast: function (expression) {
            return document.id(Slick.search(this, injectCombinator(expression, '>')).getLast());
        },

        getWindow: function () {
            return this.ownerDocument.window;
        },

        getDocument: function () {
            return this.ownerDocument;
        },

        getElementById: function (id) {
            return document.id(Slick.find(this, '#' + ('' + id).replace(/(\W)/g, '\\$1')));
        },

        match: function (expression) {
            return !expression || Slick.match(this, expression);
        }

    });

//<1.2compat>

    if (window.$$ == null) Window.implement('$$', function (selector) {
        var elements = new Elements;
        if (arguments.length == 1 && typeof selector == 'string') return Slick.search(this.document, selector, elements);
        var args = Array.flatten(arguments);
        for (var i = 0, l = args.length; i < l; i++) {
            var item = args[i];
            switch (typeOf(item)) {
                case 'element':
                    elements.push(item);
                    break;
                case 'string':
                    Slick.search(this.document, item, elements);
            }
        }
        return elements;
    });

//</1.2compat>

    if (window.$$ == null) Window.implement('$$', function (selector) {
        if (arguments.length == 1) {
            if (typeof selector == 'string') return Slick.search(this.document, selector, new Elements);
            else if (Type.isEnumerable(selector)) return new Elements(selector);
        }
        return new Elements(arguments);
    });

// Inserters

    var inserters = {

        before: function (context, element) {
            var parent = element.parentNode;
            if (parent) parent.insertBefore(context, element);
        },

        after: function (context, element) {
            var parent = element.parentNode;
            if (parent) parent.insertBefore(context, element.nextSibling);
        },

        bottom: function (context, element) {
            element.appendChild(context);
        },

        top: function (context, element) {
            element.insertBefore(context, element.firstChild);
        }

    };

    inserters.inside = inserters.bottom;

//<1.2compat>

    Object.each(inserters, function (inserter, where) {

        where = where.capitalize();

        var methods = {};

        methods['inject' + where] = function (el) {
            inserter(this, document.id(el, true));
            return this;
        };

        methods['grab' + where] = function (el) {
            inserter(document.id(el, true), this);
            return this;
        };

        Element.implement(methods);

    });

//</1.2compat>

// getProperty / setProperty

    var propertyGetters = {}, propertySetters = {};

// properties

    var properties = {};
    Array.forEach([
        'type', 'value', 'defaultValue', 'accessKey', 'cellPadding', 'cellSpacing', 'colSpan',
        'frameBorder', 'rowSpan', 'tabIndex', 'useMap'
    ], function (property) {
        properties[property.toLowerCase()] = property;
    });

    properties.html = 'innerHTML';
    properties.text = (document.createElement('div').textContent == null) ? 'innerText' : 'textContent';

    Object.forEach(properties, function (real, key) {
        propertySetters[key] = function (node, value) {
            node[real] = value;
        };
        propertyGetters[key] = function (node) {
            return node[real];
        };
    });

// Booleans

    var bools = [
        'compact', 'nowrap', 'ismap', 'declare', 'noshade', 'checked',
        'disabled', 'readOnly', 'multiple', 'selected', 'noresize',
        'defer', 'defaultChecked', 'autofocus', 'controls', 'autoplay',
        'loop'
    ];

    var booleans = {};
    Array.forEach(bools, function (bool) {
        var lower = bool.toLowerCase();
        booleans[lower] = bool;
        propertySetters[lower] = function (node, value) {
            node[bool] = !!value;
        };
        propertyGetters[lower] = function (node) {
            return !!node[bool];
        };
    });

// Special cases

    Object.append(propertySetters, {

        'class': function (node, value) {
            ('className' in node) ? node.className = (value || '') : node.setAttribute('class', value);
        },

        'for': function (node, value) {
            ('htmlFor' in node) ? node.htmlFor = value : node.setAttribute('for', value);
        },

        'style': function (node, value) {
            (node.style) ? node.style.cssText = value : node.setAttribute('style', value);
        },

        'value': function (node, value) {
            node.value = (value != null) ? value : '';
        }

    });

    propertyGetters['class'] = function (node) {
        return ('className' in node) ? node.className || null : node.getAttribute('class');
    };

    /* <webkit> */
    var el = document.createElement('button');
// IE sets type as readonly and throws
    try {
        el.type = 'button';
    } catch (e) {
    }
    if (el.type != 'button') propertySetters.type = function (node, value) {
        node.setAttribute('type', value);
    };
    el = null;
    /* </webkit> */

    /*<IE>*/
    var input = document.createElement('input');
    input.value = 't';
    input.type = 'submit';
    if (input.value != 't') propertySetters.type = function (node, type) {
        var value = node.value;
        node.type = type;
        node.value = value;
    };
    input = null;
    /*</IE>*/

    /* getProperty, setProperty */

    /* <ltIE9> */
    var pollutesGetAttribute = (function (div) {
        div.random = 'attribute';
        return (div.getAttribute('random') == 'attribute');
    })(document.createElement('div'));

    /* <ltIE9> */

    Element.implement({

        setProperty: function (name, value) {
            var setter = propertySetters[name.toLowerCase()];
            if (setter) {
                setter(this, value);
            } else {
                /* <ltIE9> */
                if (pollutesGetAttribute) var attributeWhiteList = this.retrieve('$attributeWhiteList', {});
                /* </ltIE9> */

                if (value == null) {
                    this.removeAttribute(name);
                    /* <ltIE9> */
                    if (pollutesGetAttribute) delete attributeWhiteList[name];
                    /* </ltIE9> */
                } else {
                    this.setAttribute(name, '' + value);
                    /* <ltIE9> */
                    if (pollutesGetAttribute) attributeWhiteList[name] = true;
                    /* </ltIE9> */
                }
            }
            return this;
        },

        setProperties: function (attributes) {
            for (var attribute in attributes) this.setProperty(attribute, attributes[attribute]);
            return this;
        },

        getProperty: function (name) {
            var getter = propertyGetters[name.toLowerCase()];
            if (getter) return getter(this);
            /* <ltIE9> */
            if (pollutesGetAttribute) {
                var attr = this.getAttributeNode(name), attributeWhiteList = this.retrieve('$attributeWhiteList', {});
                if (!attr) return null;
                if (attr.expando && !attributeWhiteList[name]) {
                    var outer = this.outerHTML;
                    // segment by the opening tag and find mention of attribute name
                    if (outer.substr(0, outer.search(/\/?['"]?>(?![^<]*<['"])/)).indexOf(name) < 0) return null;
                    attributeWhiteList[name] = true;
                }
            }
            /* </ltIE9> */
            var result = Slick.getAttribute(this, name);
            return (!result && !Slick.hasAttribute(this, name)) ? null : result;
        },

        getProperties: function () {
            var args = Array.from(arguments);
            return args.map(this.getProperty, this).associate(args);
        },

        removeProperty: function (name) {
            return this.setProperty(name, null);
        },

        removeProperties: function () {
            Array.each(arguments, this.removeProperty, this);
            return this;
        },

        set: function (prop, value) {
            var property = Element.Properties[prop];
            (property && property.set) ? property.set.call(this, value) : this.setProperty(prop, value);
        }.overloadSetter(),

        get: function (prop) {
            var property = Element.Properties[prop];
            return (property && property.get) ? property.get.apply(this) : this.getProperty(prop);
        }.overloadGetter(),

        erase: function (prop) {
            var property = Element.Properties[prop];
            (property && property.erase) ? property.erase.apply(this) : this.removeProperty(prop);
            return this;
        },

        hasClass: function (className) {
            return this.className.clean().contains(className, ' ');
        },

        addClass: function (className) {
            if (!this.hasClass(className)) this.className = (this.className + ' ' + className).clean();
            return this;
        },

        removeClass: function (className) {
            this.className = this.className.replace(new RegExp('(^|\\s)' + className + '(?:\\s|$)'), '$1');
            return this;
        },

        toggleClass: function (className, force) {
            if (force == null) force = !this.hasClass(className);
            return (force) ? this.addClass(className) : this.removeClass(className);
        },

        adopt: function () {
            var parent = this, fragment, elements = Array.flatten(arguments), length = elements.length;
            if (length > 1) parent = fragment = document.createDocumentFragment();

            for (var i = 0; i < length; i++) {
                var element = document.id(elements[i], true);
                if (element) parent.appendChild(element);
            }

            if (fragment) this.appendChild(fragment);

            return this;
        },

        appendText: function (text, where) {
            return this.grab(this.getDocument().newTextNode(text), where);
        },

        grab: function (el, where) {
            inserters[where || 'bottom'](document.id(el, true), this);
            return this;
        },

        inject: function (el, where) {
            inserters[where || 'bottom'](this, document.id(el, true));
            return this;
        },

        replaces: function (el) {
            el = document.id(el, true);
            el.parentNode.replaceChild(this, el);
            return this;
        },

        wraps: function (el, where) {
            el = document.id(el, true);
            return this.replaces(el).grab(el, where);
        },

        getSelected: function () {
            this.selectedIndex; // Safari 3.2.1
            return new Elements(Array.from(this.options).filter(function (option) {
                return option.selected;
            }));
        },

        toQueryString: function () {
            var queryString = [];
            this.getElements('input, select, textarea').each(function (el) {
                var type = el.type;
                if (!el.name || el.disabled || type == 'submit' || type == 'reset' || type == 'file' || type == 'image') return;

                var value = (el.get('tag') == 'select') ? el.getSelected().map(function (opt) {
                    // IE
                    return document.id(opt).get('value');
                }) : ((type == 'radio' || type == 'checkbox') && !el.checked) ? null : el.get('value');

                Array.from(value).each(function (val) {
                    if (typeof val != 'undefined') queryString.push(encodeURIComponent(el.name) + '=' + encodeURIComponent(val));
                });
            });
            return queryString.join('&');
        }

    });

    var collected = {}, storage = {};

    var get = function (uid) {
        return (storage[uid] || (storage[uid] = {}));
    };

    var clean = function (item) {
        var uid = item.uniqueNumber;
        if (item.removeEvents) item.removeEvents();
        if (item.clearAttributes) item.clearAttributes();
        if (uid != null) {
            delete collected[uid];
            delete storage[uid];
        }
        return item;
    };

    var formProps = {input: 'checked', option: 'selected', textarea: 'value'};

    Element.implement({

        destroy: function () {
            var children = clean(this).getElementsByTagName('*');
            Array.each(children, clean);
            Element.dispose(this);
            return null;
        },

        empty: function () {
            Array.from(this.childNodes).each(Element.dispose);
            return this;
        },

        dispose: function () {
            return (this.parentNode) ? this.parentNode.removeChild(this) : this;
        },

        clone: function (contents, keepid) {
            contents = contents !== false;
            var clone = this.cloneNode(contents), ce = [clone], te = [this], i;

            if (contents) {
                ce.append(Array.from(clone.getElementsByTagName('*')));
                te.append(Array.from(this.getElementsByTagName('*')));
            }

            for (i = ce.length; i--;) {
                var node = ce[i], element = te[i];
                if (!keepid) node.removeAttribute('id');
                /*<ltIE9>*/
                if (node.clearAttributes) {
                    node.clearAttributes();
                    node.mergeAttributes(element);
                    node.removeAttribute('uniqueNumber');
                    if (node.options) {
                        var no = node.options, eo = element.options;
                        for (var j = no.length; j--;) no[j].selected = eo[j].selected;
                    }
                }
                /*</ltIE9>*/
                var prop = formProps[element.tagName.toLowerCase()];
                if (prop && element[prop]) node[prop] = element[prop];
            }

            /*<ltIE9>*/
            if (Browser.ie) {
                var co = clone.getElementsByTagName('object'), to = this.getElementsByTagName('object');
                for (i = co.length; i--;) co[i].outerHTML = to[i].outerHTML;
            }
            /*</ltIE9>*/
            return document.id(clone);
        }

    });

    [Element, Window, Document].invoke('implement', {

        addListener: function (type, fn) {
            if (type == 'unload') {
                var old = fn, self = this;
                fn = function () {
                    self.removeListener('unload', fn);
                    old();
                };
            } else {
                collected[Slick.uidOf(this)] = this;
            }
            if (this.addEventListener) this.addEventListener(type, fn, !!arguments[2]);
            else this.attachEvent('on' + type, fn);
            return this;
        },

        removeListener: function (type, fn) {
            if (this.removeEventListener) this.removeEventListener(type, fn, !!arguments[2]);
            else this.detachEvent('on' + type, fn);
            return this;
        },

        retrieve: function (property, dflt) {
            var storage = get(Slick.uidOf(this)), prop = storage[property];
            if (dflt != null && prop == null) prop = storage[property] = dflt;
            return prop != null ? prop : null;
        },

        store: function (property, value) {
            var storage = get(Slick.uidOf(this));
            storage[property] = value;
            return this;
        },

        eliminate: function (property) {
            var storage = get(Slick.uidOf(this));
            delete storage[property];
            return this;
        }

    });

    /*<ltIE9>*/
    if (window.attachEvent && !window.addEventListener) window.addListener('unload', function () {
        Object.each(collected, clean);
        if (window.CollectGarbage) CollectGarbage();
    });
    /*</ltIE9>*/

    Element.Properties = {};

//<1.2compat>

    Element.Properties = new Hash;

//</1.2compat>

    Element.Properties.style = {

        set: function (style) {
            this.style.cssText = style;
        },

        get: function () {
            return this.style.cssText;
        },

        erase: function () {
            this.style.cssText = '';
        }

    };

    Element.Properties.tag = {

        get: function () {
            return this.tagName.toLowerCase();
        }

    };

    Element.Properties.html = {

        set: function (html) {
            if (html == null) html = '';
            else if (typeOf(html) == 'array') html = html.join('');
            this.innerHTML = html;
        },

        erase: function () {
            this.innerHTML = '';
        }

    };

    /*<ltIE9>*/
// technique by jdbarlett - http://jdbartlett.com/innershiv/
    var div = document.createElement('div');
    div.innerHTML = '<nav></nav>';
    var supportsHTML5Elements = (div.childNodes.length == 1);
    if (!supportsHTML5Elements) {
        var tags = 'abbr article aside audio canvas datalist details figcaption figure footer header hgroup mark meter nav output progress section summary time video'.split(' '),
            fragment = document.createDocumentFragment(), l = tags.length;
        while (l--) fragment.createElement(tags[l]);
    }
    div = null;
    /*</ltIE9>*/

    /*<IE>*/
    var supportsTableInnerHTML = Function.attempt(function () {
        var table = document.createElement('table');
        table.innerHTML = '<tr><td></td></tr>';
        return true;
    });

    /*<ltFF4>*/
    var tr = document.createElement('tr'), html = '<td></td>';
    tr.innerHTML = html;
    var supportsTRInnerHTML = (tr.innerHTML == html);
    tr = null;
    /*</ltFF4>*/

    if (!supportsTableInnerHTML || !supportsTRInnerHTML || !supportsHTML5Elements) {

        Element.Properties.html.set = (function (set) {

            var translations = {
                table: [1, '<table>', '</table>'],
                select: [1, '<select>', '</select>'],
                tbody: [2, '<table><tbody>', '</tbody></table>'],
                tr: [3, '<table><tbody><tr>', '</tr></tbody></table>']
            };

            translations.thead = translations.tfoot = translations.tbody;

            return function (html) {
                var wrap = translations[this.get('tag')];
                if (!wrap && !supportsHTML5Elements) wrap = [0, '', ''];
                if (!wrap) return set.call(this, html);

                var level = wrap[0], wrapper = document.createElement('div'), target = wrapper;
                if (!supportsHTML5Elements) fragment.appendChild(wrapper);
                wrapper.innerHTML = [wrap[1], html, wrap[2]].flatten().join('');
                while (level--) target = target.firstChild;
                this.empty().adopt(target.childNodes);
                if (!supportsHTML5Elements) fragment.removeChild(wrapper);
                wrapper = null;
            };

        })(Element.Properties.html.set);
    }
    /*</IE>*/

    /*<ltIE9>*/
    var testForm = document.createElement('form');
    testForm.innerHTML = '<select><option>s</option></select>';

    if (testForm.firstChild.value != 's') Element.Properties.value = {

        set: function (value) {
            var tag = this.get('tag');
            if (tag != 'select') return this.setProperty('value', value);
            var options = this.getElements('option');
            for (var i = 0; i < options.length; i++) {
                var option = options[i],
                    attr = option.getAttributeNode('value'),
                    optionValue = (attr && attr.specified) ? option.value : option.get('text');
                if (optionValue == value) return option.selected = true;
            }
        },

        get: function () {
            var option = this, tag = option.get('tag');

            if (tag != 'select' && tag != 'option') return this.getProperty('value');

            if (tag == 'select' && !(option = option.getSelected()[0])) return '';

            var attr = option.getAttributeNode('value');
            return (attr && attr.specified) ? option.value : option.get('text');
        }

    };
    testForm = null;
    /*</ltIE9>*/

    /*<IE>*/
    if (document.createElement('div').getAttributeNode('id')) Element.Properties.id = {
        set: function (id) {
            this.id = this.getAttributeNode('id').value = id;
        },
        get: function () {
            return this.id || null;
        },
        erase: function () {
            this.id = this.getAttributeNode('id').value = '';
        }
    };
    /*</IE>*/

})();


/*
 ---

 name: Element.Style

 description: Contains methods for interacting with the styles of Elements in a fashionable way.

 license: MIT-style license.

 requires: Element

 provides: Element.Style

 ...
 */

(function () {

    var html = document.html;

//<ltIE9>
// Check for oldIE, which does not remove styles when they're set to null
    var el = document.createElement('div');
    el.style.color = 'red';
    el.style.color = null;
    var doesNotRemoveStyles = el.style.color == 'red';
    el = null;
//</ltIE9>

    Element.Properties.styles = {set: function (styles) {
        this.setStyles(styles);
    }};

    var hasOpacity = (html.style.opacity != null),
        hasFilter = (html.style.filter != null),
        reAlpha = /alpha\(opacity=([\d.]+)\)/i;

    var setVisibility = function (element, opacity) {
        element.store('$opacity', opacity);
        element.style.visibility = opacity > 0 || opacity == null ? 'visible' : 'hidden';
    };

    var setOpacity = (hasOpacity ? function (element, opacity) {
        element.style.opacity = opacity;
    } : (hasFilter ? function (element, opacity) {
        var style = element.style;
        if (!element.currentStyle || !element.currentStyle.hasLayout) style.zoom = 1;
        if (opacity == null || opacity == 1) opacity = '';
        else opacity = 'alpha(opacity=' + (opacity * 100).limit(0, 100).round() + ')';
        var filter = style.filter || element.getComputedStyle('filter') || '';
        style.filter = reAlpha.test(filter) ? filter.replace(reAlpha, opacity) : filter + opacity;
        if (!style.filter) style.removeAttribute('filter');
    } : setVisibility));

    var getOpacity = (hasOpacity ? function (element) {
        var opacity = element.style.opacity || element.getComputedStyle('opacity');
        return (opacity == '') ? 1 : opacity.toFloat();
    } : (hasFilter ? function (element) {
        var filter = (element.style.filter || element.getComputedStyle('filter')),
            opacity;
        if (filter) opacity = filter.match(reAlpha);
        return (opacity == null || filter == null) ? 1 : (opacity[1] / 100);
    } : function (element) {
        var opacity = element.retrieve('$opacity');
        if (opacity == null) opacity = (element.style.visibility == 'hidden' ? 0 : 1);
        return opacity;
    }));

    var floatName = (html.style.cssFloat == null) ? 'styleFloat' : 'cssFloat';

    Element.implement({

        getComputedStyle: function (property) {
            if (this.currentStyle) return this.currentStyle[property.camelCase()];
            var defaultView = Element.getDocument(this).defaultView,
                computed = defaultView ? defaultView.getComputedStyle(this, null) : null;
            return (computed) ? computed.getPropertyValue((property == floatName) ? 'float' : property.hyphenate()) : null;
        },

        setStyle: function (property, value) {
            if (property == 'opacity') {
                if (value != null) value = parseFloat(value);
                setOpacity(this, value);
                return this;
            }
            property = (property == 'float' ? floatName : property).camelCase();
            if (typeOf(value) != 'string') {
                var map = (Element.Styles[property] || '@').split(' ');
                value = Array.from(value).map(function (val, i) {
                    if (!map[i]) return '';
                    return (typeOf(val) == 'number') ? map[i].replace('@', Math.round(val)) : val;
                }).join(' ');
            } else if (value == String(Number(value))) {
                value = Math.round(value);
            }
            this.style[property] = value;
            //<ltIE9>
            if ((value == '' || value == null) && doesNotRemoveStyles && this.style.removeAttribute) {
                this.style.removeAttribute(property);
            }
            //</ltIE9>
            return this;
        },

        getStyle: function (property) {
            if (property == 'opacity') return getOpacity(this);
            property = (property == 'float' ? floatName : property).camelCase();
            var result = this.style[property];
            if (!result || property == 'zIndex') {
                result = [];
                for (var style in Element.ShortStyles) {
                    if (property != style) continue;
                    for (var s in Element.ShortStyles[style]) result.push(this.getStyle(s));
                    return result.join(' ');
                }
                result = this.getComputedStyle(property);
            }
            if (result) {
                result = String(result);
                var color = result.match(/rgba?\([\d\s,]+\)/);
                if (color) result = result.replace(color[0], color[0].rgbToHex());
            }
            if (Browser.opera || Browser.ie) {
                if ((/^(height|width)$/).test(property) && !(/px$/.test(result))) {
                    var values = (property == 'width') ? ['left', 'right'] : ['top', 'bottom'], size = 0;
                    values.each(function (value) {
                        size += this.getStyle('border-' + value + '-width').toInt() + this.getStyle('padding-' + value).toInt();
                    }, this);
                    return this['offset' + property.capitalize()] - size + 'px';
                }
                if (Browser.ie && (/^border(.+)Width|margin|padding/).test(property) && isNaN(parseFloat(result))) {
                    return '0px';
                }
            }
            return result;
        },

        setStyles: function (styles) {
            for (var style in styles) this.setStyle(style, styles[style]);
            return this;
        },

        getStyles: function () {
            var result = {};
            Array.flatten(arguments).each(function (key) {
                result[key] = this.getStyle(key);
            }, this);
            return result;
        }

    });

    Element.Styles = {
        left: '@px', top: '@px', bottom: '@px', right: '@px',
        width: '@px', height: '@px', maxWidth: '@px', maxHeight: '@px', minWidth: '@px', minHeight: '@px',
        backgroundColor: 'rgb(@, @, @)', backgroundPosition: '@px @px', color: 'rgb(@, @, @)',
        fontSize: '@px', letterSpacing: '@px', lineHeight: '@px', clip: 'rect(@px @px @px @px)',
        margin: '@px @px @px @px', padding: '@px @px @px @px', border: '@px @ rgb(@, @, @) @px @ rgb(@, @, @) @px @ rgb(@, @, @)',
        borderWidth: '@px @px @px @px', borderStyle: '@ @ @ @', borderColor: 'rgb(@, @, @) rgb(@, @, @) rgb(@, @, @) rgb(@, @, @)',
        zIndex: '@', 'zoom': '@', fontWeight: '@', textIndent: '@px', opacity: '@'
    };

//<1.3compat>

    Element.implement({

        setOpacity: function (value) {
            setOpacity(this, value);
            return this;
        },

        getOpacity: function () {
            return getOpacity(this);
        }

    });

    Element.Properties.opacity = {

        set: function (opacity) {
            setOpacity(this, opacity);
            setVisibility(this, opacity);
        },

        get: function () {
            return getOpacity(this);
        }

    };

//</1.3compat>

//<1.2compat>

    Element.Styles = new Hash(Element.Styles);

//</1.2compat>

    Element.ShortStyles = {margin: {}, padding: {}, border: {}, borderWidth: {}, borderStyle: {}, borderColor: {}};

    ['Top', 'Right', 'Bottom', 'Left'].each(function (direction) {
        var Short = Element.ShortStyles;
        var All = Element.Styles;
        ['margin', 'padding'].each(function (style) {
            var sd = style + direction;
            Short[style][sd] = All[sd] = '@px';
        });
        var bd = 'border' + direction;
        Short.border[bd] = All[bd] = '@px @ rgb(@, @, @)';
        var bdw = bd + 'Width', bds = bd + 'Style', bdc = bd + 'Color';
        Short[bd] = {};
        Short.borderWidth[bdw] = Short[bd][bdw] = All[bdw] = '@px';
        Short.borderStyle[bds] = Short[bd][bds] = All[bds] = '@';
        Short.borderColor[bdc] = Short[bd][bdc] = All[bdc] = 'rgb(@, @, @)';
    });

})();


/*
 ---

 name: Element.Event

 description: Contains Element methods for dealing with events. This file also includes mouseenter and mouseleave custom Element Events, if necessary.

 license: MIT-style license.

 requires: [Element, Event]

 provides: Element.Event

 ...
 */

(function () {

    Element.Properties.events = {set: function (events) {
        this.addEvents(events);
    }};

    [Element, Window, Document].invoke('implement', {

        addEvent: function (type, fn) {
            var events = this.retrieve('events', {});
            if (!events[type]) events[type] = {keys: [], values: []};
            if (events[type].keys.contains(fn)) return this;
            events[type].keys.push(fn);
            var realType = type,
                custom = Element.Events[type],
                condition = fn,
                self = this;
            if (custom) {
                if (custom.onAdd) custom.onAdd.call(this, fn, type);
                if (custom.condition) {
                    condition = function (event) {
                        if (custom.condition.call(this, event, type)) return fn.call(this, event);
                        return true;
                    };
                }
                if (custom.base) realType = Function.from(custom.base).call(this, type);
            }
            var defn = function () {
                return fn.call(self);
            };
            var nativeEvent = Element.NativeEvents[realType];
            if (nativeEvent) {
                if (nativeEvent == 2) {
                    defn = function (event) {
                        event = new DOMEvent(event, self.getWindow());
                        if (condition.call(self, event) === false) event.stop();
                    };
                }
                this.addListener(realType, defn, arguments[2]);
            }
            events[type].values.push(defn);
            return this;
        },

        removeEvent: function (type, fn) {
            var events = this.retrieve('events');
            if (!events || !events[type]) return this;
            var list = events[type];
            var index = list.keys.indexOf(fn);
            if (index == -1) return this;
            var value = list.values[index];
            delete list.keys[index];
            delete list.values[index];
            var custom = Element.Events[type];
            if (custom) {
                if (custom.onRemove) custom.onRemove.call(this, fn, type);
                if (custom.base) type = Function.from(custom.base).call(this, type);
            }
            return (Element.NativeEvents[type]) ? this.removeListener(type, value, arguments[2]) : this;
        },

        addEvents: function (events) {
            for (var event in events) this.addEvent(event, events[event]);
            return this;
        },

        removeEvents: function (events) {
            var type;
            if (typeOf(events) == 'object') {
                for (type in events) this.removeEvent(type, events[type]);
                return this;
            }
            var attached = this.retrieve('events');
            if (!attached) return this;
            if (!events) {
                for (type in attached) this.removeEvents(type);
                this.eliminate('events');
            } else if (attached[events]) {
                attached[events].keys.each(function (fn) {
                    this.removeEvent(events, fn);
                }, this);
                delete attached[events];
            }
            return this;
        },

        fireEvent: function (type, args, delay) {
            var events = this.retrieve('events');
            if (!events || !events[type]) return this;
            args = Array.from(args);

            events[type].keys.each(function (fn) {
                if (delay) fn.delay(delay, this, args);
                else fn.apply(this, args);
            }, this);
            return this;
        },

        cloneEvents: function (from, type) {
            from = document.id(from);
            var events = from.retrieve('events');
            if (!events) return this;
            if (!type) {
                for (var eventType in events) this.cloneEvents(from, eventType);
            } else if (events[type]) {
                events[type].keys.each(function (fn) {
                    this.addEvent(type, fn);
                }, this);
            }
            return this;
        }

    });

    Element.NativeEvents = {
        click: 2, dblclick: 2, mouseup: 2, mousedown: 2, contextmenu: 2, //mouse buttons
        mousewheel: 2, DOMMouseScroll: 2, //mouse wheel
        mouseover: 2, mouseout: 2, mousemove: 2, selectstart: 2, selectend: 2, //mouse movement
        keydown: 2, keypress: 2, keyup: 2, //keyboard
        orientationchange: 2, // mobile
        touchstart: 2, touchmove: 2, touchend: 2, touchcancel: 2, // touch
        gesturestart: 2, gesturechange: 2, gestureend: 2, // gesture
        focus: 2, blur: 2, change: 2, reset: 2, select: 2, submit: 2, paste: 2, input: 2, //form elements
        load: 2, unload: 1, beforeunload: 2, resize: 1, move: 1, DOMContentLoaded: 1, readystatechange: 1, //window
        error: 1, abort: 1, scroll: 1 //misc
    };

    Element.Events = {mousewheel: {
        base: (Browser.firefox) ? 'DOMMouseScroll' : 'mousewheel'
    }};

    if ('onmouseenter' in document.documentElement) {
        Element.NativeEvents.mouseenter = Element.NativeEvents.mouseleave = 2;
    } else {
        var check = function (event) {
            var related = event.relatedTarget;
            if (related == null) return true;
            if (!related) return false;
            return (related != this && related.prefix != 'xul' && typeOf(this) != 'document' && !this.contains(related));
        };

        Element.Events.mouseenter = {
            base: 'mouseover',
            condition: check
        };

        Element.Events.mouseleave = {
            base: 'mouseout',
            condition: check
        };
    }

    /*<ltIE9>*/
    if (!window.addEventListener) {
        Element.NativeEvents.propertychange = 2;
        Element.Events.change = {
            base: function () {
                var type = this.type;
                return (this.get('tag') == 'input' && (type == 'radio' || type == 'checkbox')) ? 'propertychange' : 'change'
            },
            condition: function (event) {
                return this.type != 'radio' || (event.event.propertyName == 'checked' && this.checked);
            }
        }
    }
    /*</ltIE9>*/

//<1.2compat>

    Element.Events = new Hash(Element.Events);

//</1.2compat>

})();


/*
 ---

 name: Element.Delegation

 description: Extends the Element native object to include the delegate method for more efficient event management.

 license: MIT-style license.

 requires: [Element.Event]

 provides: [Element.Delegation]

 ...
 */

(function () {

    var eventListenerSupport = !!window.addEventListener;

    Element.NativeEvents.focusin = Element.NativeEvents.focusout = 2;

    var bubbleUp = function (self, match, fn, event, target) {
        while (target && target != self) {
            if (match(target, event)) return fn.call(target, event, target);
            target = document.id(target.parentNode);
        }
    };

    var map = {
        mouseenter: {
            base: 'mouseover'
        },
        mouseleave: {
            base: 'mouseout'
        },
        focus: {
            base: 'focus' + (eventListenerSupport ? '' : 'in'),
            capture: true
        },
        blur: {
            base: eventListenerSupport ? 'blur' : 'focusout',
            capture: true
        }
    };

    /*<ltIE9>*/
    var _key = '$delegation:';
    var formObserver = function (type) {

        return {

            base: 'focusin',

            remove: function (self, uid) {
                var list = self.retrieve(_key + type + 'listeners', {})[uid];
                if (list && list.forms) for (var i = list.forms.length; i--;) {
                    list.forms[i].removeEvent(type, list.fns[i]);
                }
            },

            listen: function (self, match, fn, event, target, uid) {
                var form = (target.get('tag') == 'form') ? target : event.target.getParent('form');
                if (!form) return;

                var listeners = self.retrieve(_key + type + 'listeners', {}),
                    listener = listeners[uid] || {forms: [], fns: []},
                    forms = listener.forms, fns = listener.fns;

                if (forms.indexOf(form) != -1) return;
                forms.push(form);

                var _fn = function (event) {
                    bubbleUp(self, match, fn, event, target);
                };
                form.addEvent(type, _fn);
                fns.push(_fn);

                listeners[uid] = listener;
                self.store(_key + type + 'listeners', listeners);
            }
        };
    };

    var inputObserver = function (type) {
        return {
            base: 'focusin',
            listen: function (self, match, fn, event, target) {
                var events = {blur: function () {
                    this.removeEvents(events);
                }};
                events[type] = function (event) {
                    bubbleUp(self, match, fn, event, target);
                };
                event.target.addEvents(events);
            }
        };
    };

    if (!eventListenerSupport) Object.append(map, {
        submit: formObserver('submit'),
        reset: formObserver('reset'),
        change: inputObserver('change'),
        select: inputObserver('select')
    });
    /*</ltIE9>*/

    var proto = Element.prototype,
        addEvent = proto.addEvent,
        removeEvent = proto.removeEvent;

    var relay = function (old, method) {
        return function (type, fn, useCapture) {
            if (type.indexOf(':relay') == -1) return old.call(this, type, fn, useCapture);
            var parsed = Slick.parse(type).expressions[0][0];
            if (parsed.pseudos[0].key != 'relay') return old.call(this, type, fn, useCapture);
            var newType = parsed.tag;
            parsed.pseudos.slice(1).each(function (pseudo) {
                newType += ':' + pseudo.key + (pseudo.value ? '(' + pseudo.value + ')' : '');
            });
            old.call(this, type, fn);
            return method.call(this, newType, parsed.pseudos[0].value, fn);
        };
    };

    var delegation = {

        addEvent: function (type, match, fn) {
            var storage = this.retrieve('$delegates', {}), stored = storage[type];
            if (stored) for (var _uid in stored) {
                if (stored[_uid].fn == fn && stored[_uid].match == match) return this;
            }

            var _type = type, _match = match, _fn = fn, _map = map[type] || {};
            type = _map.base || _type;

            match = function (target) {
                return Slick.match(target, _match);
            };

            var elementEvent = Element.Events[_type];
            if (elementEvent && elementEvent.condition) {
                var __match = match, condition = elementEvent.condition;
                match = function (target, event) {
                    return __match(target, event) && condition.call(target, event, type);
                };
            }

            var self = this, uid = String.uniqueID();
            var delegator = _map.listen ? function (event, target) {
                if (!target && event && event.target) target = event.target;
                if (target) _map.listen(self, match, fn, event, target, uid);
            } : function (event, target) {
                if (!target && event && event.target) target = event.target;
                if (target) bubbleUp(self, match, fn, event, target);
            };

            if (!stored) stored = {};
            stored[uid] = {
                match: _match,
                fn: _fn,
                delegator: delegator
            };
            storage[_type] = stored;
            return addEvent.call(this, type, delegator, _map.capture);
        },

        removeEvent: function (type, match, fn, _uid) {
            var storage = this.retrieve('$delegates', {}), stored = storage[type];
            if (!stored) return this;

            if (_uid) {
                var _type = type, delegator = stored[_uid].delegator, _map = map[type] || {};
                type = _map.base || _type;
                if (_map.remove) _map.remove(this, _uid);
                delete stored[_uid];
                storage[_type] = stored;
                return removeEvent.call(this, type, delegator);
            }

            var __uid, s;
            if (fn) for (__uid in stored) {
                s = stored[__uid];
                if (s.match == match && s.fn == fn) return delegation.removeEvent.call(this, type, match, fn, __uid);
            } else for (__uid in stored) {
                s = stored[__uid];
                if (s.match == match) delegation.removeEvent.call(this, type, match, s.fn, __uid);
            }
            return this;
        }

    };

    [Element, Window, Document].invoke('implement', {
        addEvent: relay(addEvent, delegation.addEvent),
        removeEvent: relay(removeEvent, delegation.removeEvent)
    });

})();


/*
 ---

 name: Element.Dimensions

 description: Contains methods to work with size, scroll, or positioning of Elements and the window object.

 license: MIT-style license.

 credits:
 - Element positioning based on the [qooxdoo](http://qooxdoo.org/) code and smart browser fixes, [LGPL License](http://www.gnu.org/licenses/lgpl.html).
 - Viewport dimensions based on [YUI](http://developer.yahoo.com/yui/) code, [BSD License](http://developer.yahoo.com/yui/license.html).

 requires: [Element, Element.Style]

 provides: [Element.Dimensions]

 ...
 */

(function () {

    var element = document.createElement('div'),
        child = document.createElement('div');
    element.style.height = '0';
    element.appendChild(child);
    var brokenOffsetParent = (child.offsetParent === element);
    element = child = null;

    var isOffset = function (el) {
        return styleString(el, 'position') != 'static' || isBody(el);
    };

    var isOffsetStatic = function (el) {
        return isOffset(el) || (/^(?:table|td|th)$/i).test(el.tagName);
    };

    Element.implement({

        scrollTo: function (x, y) {
            if (isBody(this)) {
                this.getWindow().scrollTo(x, y);
            } else {
                this.scrollLeft = x;
                this.scrollTop = y;
            }
            return this;
        },

        getSize: function () {
            if (isBody(this)) return this.getWindow().getSize();
            return {x: this.offsetWidth, y: this.offsetHeight};
        },

        getScrollSize: function () {
            if (isBody(this)) return this.getWindow().getScrollSize();
            return {x: this.scrollWidth, y: this.scrollHeight};
        },

        getScroll: function () {
            if (isBody(this)) return this.getWindow().getScroll();
            return {x: this.scrollLeft, y: this.scrollTop};
        },

        getScrolls: function () {
            var element = this.parentNode, position = {x: 0, y: 0};
            while (element && !isBody(element)) {
                position.x += element.scrollLeft;
                position.y += element.scrollTop;
                element = element.parentNode;
            }
            return position;
        },

        getOffsetParent: brokenOffsetParent ? function () {
            var element = this;
            if (isBody(element) || styleString(element, 'position') == 'fixed') return null;

            var isOffsetCheck = (styleString(element, 'position') == 'static') ? isOffsetStatic : isOffset;
            while ((element = element.parentNode)) {
                if (isOffsetCheck(element)) return element;
            }
            return null;
        } : function () {
            var element = this;
            if (isBody(element) || styleString(element, 'position') == 'fixed') return null;

            try {
                return element.offsetParent;
            } catch (e) {
            }
            return null;
        },

        getOffsets: function () {
            if (this.getBoundingClientRect && !Browser.Platform.ios) {
                var bound = this.getBoundingClientRect(),
                    html = document.id(this.getDocument().documentElement),
                    htmlScroll = html.getScroll(),
                    elemScrolls = this.getScrolls(),
                    isFixed = (styleString(this, 'position') == 'fixed');

                return {
                    x: bound.left.toInt() + elemScrolls.x + ((isFixed) ? 0 : htmlScroll.x) - html.clientLeft,
                    y: bound.top.toInt() + elemScrolls.y + ((isFixed) ? 0 : htmlScroll.y) - html.clientTop
                };
            }

            var element = this, position = {x: 0, y: 0};
            if (isBody(this)) return position;

            while (element && !isBody(element)) {
                position.x += element.offsetLeft;
                position.y += element.offsetTop;

                if (Browser.firefox) {
                    if (!borderBox(element)) {
                        position.x += leftBorder(element);
                        position.y += topBorder(element);
                    }
                    var parent = element.parentNode;
                    if (parent && styleString(parent, 'overflow') != 'visible') {
                        position.x += leftBorder(parent);
                        position.y += topBorder(parent);
                    }
                } else if (element != this && Browser.safari) {
                    position.x += leftBorder(element);
                    position.y += topBorder(element);
                }

                element = element.offsetParent;
            }
            if (Browser.firefox && !borderBox(this)) {
                position.x -= leftBorder(this);
                position.y -= topBorder(this);
            }
            return position;
        },

        getPosition: function (relative) {
            var offset = this.getOffsets(),
                scroll = this.getScrolls();
            var position = {
                x: offset.x - scroll.x,
                y: offset.y - scroll.y
            };

            if (relative && (relative = document.id(relative))) {
                var relativePosition = relative.getPosition();
                return {x: position.x - relativePosition.x - leftBorder(relative), y: position.y - relativePosition.y - topBorder(relative)};
            }
            return position;
        },

        getCoordinates: function (element) {
            if (isBody(this)) return this.getWindow().getCoordinates();
            var position = this.getPosition(element),
                size = this.getSize();
            var obj = {
                left: position.x,
                top: position.y,
                width: size.x,
                height: size.y
            };
            obj.right = obj.left + obj.width;
            obj.bottom = obj.top + obj.height;
            return obj;
        },

        computePosition: function (obj) {
            return {
                left: obj.x - styleNumber(this, 'margin-left'),
                top: obj.y - styleNumber(this, 'margin-top')
            };
        },

        setPosition: function (obj) {
            return this.setStyles(this.computePosition(obj));
        }

    });


    [Document, Window].invoke('implement', {

        getSize: function () {
            var doc = getCompatElement(this);
            return {x: doc.clientWidth, y: doc.clientHeight};
        },

        getScroll: function () {
            var win = this.getWindow(), doc = getCompatElement(this);
            return {x: win.pageXOffset || doc.scrollLeft, y: win.pageYOffset || doc.scrollTop};
        },

        getScrollSize: function () {
            var doc = getCompatElement(this),
                min = this.getSize(),
                body = this.getDocument().body;

            return {x: Math.max(doc.scrollWidth, body.scrollWidth, min.x), y: Math.max(doc.scrollHeight, body.scrollHeight, min.y)};
        },

        getPosition: function () {
            return {x: 0, y: 0};
        },

        getCoordinates: function () {
            var size = this.getSize();
            return {top: 0, left: 0, bottom: size.y, right: size.x, height: size.y, width: size.x};
        }

    });

// private methods

    var styleString = Element.getComputedStyle;

    function styleNumber(element, style) {
        return styleString(element, style).toInt() || 0;
    }

    function borderBox(element) {
        return styleString(element, '-moz-box-sizing') == 'border-box';
    }

    function topBorder(element) {
        return styleNumber(element, 'border-top-width');
    }

    function leftBorder(element) {
        return styleNumber(element, 'border-left-width');
    }

    function isBody(element) {
        return (/^(?:body|html)$/i).test(element.tagName);
    }

    function getCompatElement(element) {
        var doc = element.getDocument();
        return (!doc.compatMode || doc.compatMode == 'CSS1Compat') ? doc.html : doc.body;
    }

})();

//aliases
Element.alias({position: 'setPosition'}); //compatability

[Window, Document, Element].invoke('implement', {

    getHeight: function () {
        return this.getSize().y;
    },

    getWidth: function () {
        return this.getSize().x;
    },

    getScrollTop: function () {
        return this.getScroll().y;
    },

    getScrollLeft: function () {
        return this.getScroll().x;
    },

    getScrollHeight: function () {
        return this.getScrollSize().y;
    },

    getScrollWidth: function () {
        return this.getScrollSize().x;
    },

    getTop: function () {
        return this.getPosition().y;
    },

    getLeft: function () {
        return this.getPosition().x;
    }

});


/*
 ---

 name: Fx

 description: Contains the basic animation logic to be extended by all other Fx Classes.

 license: MIT-style license.

 requires: [Chain, Events, Options]

 provides: Fx

 ...
 */

(function () {

    var Fx = this.Fx = new Class({

        Implements: [Chain, Events, Options],

        options: {
            /*
             onStart: nil,
             onCancel: nil,
             onComplete: nil,
             */
            fps: 60,
            unit: false,
            duration: 500,
            frames: null,
            frameSkip: true,
            link: 'ignore'
        },

        initialize: function (options) {
            this.subject = this.subject || this;
            this.setOptions(options);
        },

        getTransition: function () {
            return function (p) {
                return -(Math.cos(Math.PI * p) - 1) / 2;
            };
        },

        step: function (now) {
            if (this.options.frameSkip) {
                var diff = (this.time != null) ? (now - this.time) : 0, frames = diff / this.frameInterval;
                this.time = now;
                this.frame += frames;
            } else {
                this.frame++;
            }

            if (this.frame < this.frames) {
                var delta = this.transition(this.frame / this.frames);
                this.set(this.compute(this.from, this.to, delta));
            } else {
                this.frame = this.frames;
                this.set(this.compute(this.from, this.to, 1));
                this.stop();
            }
        },

        set: function (now) {
            return now;
        },

        compute: function (from, to, delta) {
            return Fx.compute(from, to, delta);
        },

        check: function () {
            if (!this.isRunning()) return true;
            switch (this.options.link) {
                case 'cancel':
                    this.cancel();
                    return true;
                case 'chain':
                    this.chain(this.caller.pass(arguments, this));
                    return false;
            }
            return false;
        },

        start: function (from, to) {
            if (!this.check(from, to)) return this;
            this.from = from;
            this.to = to;
            this.frame = (this.options.frameSkip) ? 0 : -1;
            this.time = null;
            this.transition = this.getTransition();
            var frames = this.options.frames, fps = this.options.fps, duration = this.options.duration;
            this.duration = Fx.Durations[duration] || duration.toInt();
            this.frameInterval = 1000 / fps;
            this.frames = frames || Math.round(this.duration / this.frameInterval);
            this.fireEvent('start', this.subject);
            pushInstance.call(this, fps);
            return this;
        },

        stop: function () {
            if (this.isRunning()) {
                this.time = null;
                pullInstance.call(this, this.options.fps);
                if (this.frames == this.frame) {
                    this.fireEvent('complete', this.subject);
                    if (!this.callChain()) this.fireEvent('chainComplete', this.subject);
                } else {
                    this.fireEvent('stop', this.subject);
                }
            }
            return this;
        },

        cancel: function () {
            if (this.isRunning()) {
                this.time = null;
                pullInstance.call(this, this.options.fps);
                this.frame = this.frames;
                this.fireEvent('cancel', this.subject).clearChain();
            }
            return this;
        },

        pause: function () {
            if (this.isRunning()) {
                this.time = null;
                pullInstance.call(this, this.options.fps);
            }
            return this;
        },

        resume: function () {
            if ((this.frame < this.frames) && !this.isRunning()) pushInstance.call(this, this.options.fps);
            return this;
        },

        isRunning: function () {
            var list = instances[this.options.fps];
            return list && list.contains(this);
        }

    });

    Fx.compute = function (from, to, delta) {
        return (to - from) * delta + from;
    };

    Fx.Durations = {'short': 250, 'normal': 500, 'long': 1000};

// global timers

    var instances = {}, timers = {};

    var loop = function () {
        var now = Date.now();
        for (var i = this.length; i--;) {
            var instance = this[i];
            if (instance) instance.step(now);
        }
    };

    var pushInstance = function (fps) {
        var list = instances[fps] || (instances[fps] = []);
        list.push(this);
        if (!timers[fps]) timers[fps] = loop.periodical(Math.round(1000 / fps), list);
    };

    var pullInstance = function (fps) {
        var list = instances[fps];
        if (list) {
            list.erase(this);
            if (!list.length && timers[fps]) {
                delete instances[fps];
                timers[fps] = clearInterval(timers[fps]);
            }
        }
    };

})();


/*
 ---

 name: Fx.CSS

 description: Contains the CSS animation logic. Used by Fx.Tween, Fx.Morph, Fx.Elements.

 license: MIT-style license.

 requires: [Fx, Element.Style]

 provides: Fx.CSS

 ...
 */

Fx.CSS = new Class({

    Extends: Fx,

    //prepares the base from/to object

    prepare: function (element, property, values) {
        values = Array.from(values);
        var from = values[0], to = values[1];
        if (to == null) {
            to = from;
            from = element.getStyle(property);
            var unit = this.options.unit;
            // adapted from: https://github.com/ryanmorr/fx/blob/master/fx.js#L299
            if (unit && from.slice(-unit.length) != unit && parseFloat(from) != 0) {
                element.setStyle(property, to + unit);
                var value = element.getComputedStyle(property);
                // IE and Opera support pixelLeft or pixelWidth
                if (!(/px$/.test(value))) {
                    value = element.style[('pixel-' + property).camelCase()];
                    if (value == null) {
                        // adapted from Dean Edwards' http://erik.eae.net/archives/2007/07/27/18.54.15/#comment-102291
                        var left = element.style.left;
                        element.style.left = to + unit;
                        value = element.style.pixelLeft;
                        element.style.left = left;
                    }
                }
                from = (to || 1) / (parseFloat(value) || 1) * (parseFloat(from) || 0);
                element.setStyle(property, from + unit);
            }
        }
        return {from: this.parse(from), to: this.parse(to)};
    },

    //parses a value into an array

    parse: function (value) {
        value = Function.from(value)();
        value = (typeof value == 'string') ? value.split(' ') : Array.from(value);
        return value.map(function (val) {
            val = String(val);
            var found = false;
            Object.each(Fx.CSS.Parsers, function (parser, key) {
                if (found) return;
                var parsed = parser.parse(val);
                if (parsed || parsed === 0) found = {value: parsed, parser: parser};
            });
            found = found || {value: val, parser: Fx.CSS.Parsers.String};
            return found;
        });
    },

    //computes by a from and to prepared objects, using their parsers.

    compute: function (from, to, delta) {
        var computed = [];
        (Math.min(from.length, to.length)).times(function (i) {
            computed.push({value: from[i].parser.compute(from[i].value, to[i].value, delta), parser: from[i].parser});
        });
        computed.$family = Function.from('fx:css:value');
        return computed;
    },

    //serves the value as settable

    serve: function (value, unit) {
        if (typeOf(value) != 'fx:css:value') value = this.parse(value);
        var returned = [];
        value.each(function (bit) {
            returned = returned.concat(bit.parser.serve(bit.value, unit));
        });
        return returned;
    },

    //renders the change to an element

    render: function (element, property, value, unit) {
        element.setStyle(property, this.serve(value, unit));
    },

    //searches inside the page css to find the values for a selector

    search: function (selector) {
        if (Fx.CSS.Cache[selector]) return Fx.CSS.Cache[selector];
        var to = {}, selectorTest = new RegExp('^' + selector.escapeRegExp() + '$');
        Array.each(document.styleSheets, function (sheet, j) {
            var href = sheet.href;
            if (href && href.contains('://') && !href.contains(document.domain)) return;
            var rules = sheet.rules || sheet.cssRules;
            Array.each(rules, function (rule, i) {
                if (!rule.style) return;
                var selectorText = (rule.selectorText) ? rule.selectorText.replace(/^\w+/, function (m) {
                    return m.toLowerCase();
                }) : null;
                if (!selectorText || !selectorTest.test(selectorText)) return;
                Object.each(Element.Styles, function (value, style) {
                    if (!rule.style[style] || Element.ShortStyles[style]) return;
                    value = String(rule.style[style]);
                    to[style] = ((/^rgb/).test(value)) ? value.rgbToHex() : value;
                });
            });
        });
        return Fx.CSS.Cache[selector] = to;
    }

});

Fx.CSS.Cache = {};

Fx.CSS.Parsers = {

    Color: {
        parse: function (value) {
            if (value.match(/^#[0-9a-f]{3,6}$/i)) return value.hexToRgb(true);
            return ((value = value.match(/(\d+),\s*(\d+),\s*(\d+)/))) ? [value[1], value[2], value[3]] : false;
        },
        compute: function (from, to, delta) {
            return from.map(function (value, i) {
                return Math.round(Fx.compute(from[i], to[i], delta));
            });
        },
        serve: function (value) {
            return value.map(Number);
        }
    },

    Number: {
        parse: parseFloat,
        compute: Fx.compute,
        serve: function (value, unit) {
            return (unit) ? value + unit : value;
        }
    },

    String: {
        parse: Function.from(false),
        compute: function (zero, one) {
            return one;
        },
        serve: function (zero) {
            return zero;
        }
    }

};

//<1.2compat>

Fx.CSS.Parsers = new Hash(Fx.CSS.Parsers);

//</1.2compat>


/*
 ---

 name: Fx.Tween

 description: Formerly Fx.Style, effect to transition any CSS property for an element.

 license: MIT-style license.

 requires: Fx.CSS

 provides: [Fx.Tween, Element.fade, Element.highlight]

 ...
 */

Fx.Tween = new Class({

    Extends: Fx.CSS,

    initialize: function (element, options) {
        this.element = this.subject = document.id(element);
        this.parent(options);
    },

    set: function (property, now) {
        if (arguments.length == 1) {
            now = property;
            property = this.property || this.options.property;
        }
        this.render(this.element, property, now, this.options.unit);
        return this;
    },

    start: function (property, from, to) {
        if (!this.check(property, from, to)) return this;
        var args = Array.flatten(arguments);
        this.property = this.options.property || args.shift();
        var parsed = this.prepare(this.element, this.property, args);
        return this.parent(parsed.from, parsed.to);
    }

});

Element.Properties.tween = {

    set: function (options) {
        this.get('tween').cancel().setOptions(options);
        return this;
    },

    get: function () {
        var tween = this.retrieve('tween');
        if (!tween) {
            tween = new Fx.Tween(this, {link: 'cancel'});
            this.store('tween', tween);
        }
        return tween;
    }

};

Element.implement({

    tween: function (property, from, to) {
        this.get('tween').start(property, from, to);
        return this;
    },

    fade: function (how) {
        var fade = this.get('tween'), method, args = ['opacity'].append(arguments), toggle;
        if (args[1] == null) args[1] = 'toggle';
        switch (args[1]) {
            case 'in':
                method = 'start';
                args[1] = 1;
                break;
            case 'out':
                method = 'start';
                args[1] = 0;
                break;
            case 'show':
                method = 'set';
                args[1] = 1;
                break;
            case 'hide':
                method = 'set';
                args[1] = 0;
                break;
            case 'toggle':
                var flag = this.retrieve('fade:flag', this.getStyle('opacity') == 1);
                method = 'start';
                args[1] = flag ? 0 : 1;
                this.store('fade:flag', !flag);
                toggle = true;
                break;
            default:
                method = 'start';
        }
        if (!toggle) this.eliminate('fade:flag');
        fade[method].apply(fade, args);
        var to = args[args.length - 1];
        if (method == 'set' || to != 0) this.setStyle('visibility', to == 0 ? 'hidden' : 'visible');
        else fade.chain(function () {
            this.element.setStyle('visibility', 'hidden');
            this.callChain();
        });
        return this;
    },

    highlight: function (start, end) {
        if (!end) {
            end = this.retrieve('highlight:original', this.getStyle('background-color'));
            end = (end == 'transparent') ? '#fff' : end;
        }
        var tween = this.get('tween');
        tween.start('background-color', start || '#ffff88', end).chain(function () {
            this.setStyle('background-color', this.retrieve('highlight:original'));
            tween.callChain();
        }.bind(this));
        return this;
    }

});


/*
 ---

 name: Fx.Morph

 description: Formerly Fx.Styles, effect to transition any number of CSS properties for an element using an object of rules, or CSS based selector rules.

 license: MIT-style license.

 requires: Fx.CSS

 provides: Fx.Morph

 ...
 */

Fx.Morph = new Class({

    Extends: Fx.CSS,

    initialize: function (element, options) {
        this.element = this.subject = document.id(element);
        this.parent(options);
    },

    set: function (now) {
        if (typeof now == 'string') now = this.search(now);
        for (var p in now) this.render(this.element, p, now[p], this.options.unit);
        return this;
    },

    compute: function (from, to, delta) {
        var now = {};
        for (var p in from) now[p] = this.parent(from[p], to[p], delta);
        return now;
    },

    start: function (properties) {
        if (!this.check(properties)) return this;
        if (typeof properties == 'string') properties = this.search(properties);
        var from = {}, to = {};
        for (var p in properties) {
            var parsed = this.prepare(this.element, p, properties[p]);
            from[p] = parsed.from;
            to[p] = parsed.to;
        }
        return this.parent(from, to);
    }

});

Element.Properties.morph = {

    set: function (options) {
        this.get('morph').cancel().setOptions(options);
        return this;
    },

    get: function () {
        var morph = this.retrieve('morph');
        if (!morph) {
            morph = new Fx.Morph(this, {link: 'cancel'});
            this.store('morph', morph);
        }
        return morph;
    }

};

Element.implement({

    morph: function (props) {
        this.get('morph').start(props);
        return this;
    }

});


/*
 ---

 name: Fx.Transitions

 description: Contains a set of advanced transitions to be used with any of the Fx Classes.

 license: MIT-style license.

 credits:
 - Easing Equations by Robert Penner, <http://www.robertpenner.com/easing/>, modified and optimized to be used with MooTools.

 requires: Fx

 provides: Fx.Transitions

 ...
 */

Fx.implement({

    getTransition: function () {
        var trans = this.options.transition || Fx.Transitions.Sine.easeInOut;
        if (typeof trans == 'string') {
            var data = trans.split(':');
            trans = Fx.Transitions;
            trans = trans[data[0]] || trans[data[0].capitalize()];
            if (data[1]) trans = trans['ease' + data[1].capitalize() + (data[2] ? data[2].capitalize() : '')];
        }
        return trans;
    }

});

Fx.Transition = function (transition, params) {
    params = Array.from(params);
    var easeIn = function (pos) {
        return transition(pos, params);
    };
    return Object.append(easeIn, {
        easeIn: easeIn,
        easeOut: function (pos) {
            return 1 - transition(1 - pos, params);
        },
        easeInOut: function (pos) {
            return (pos <= 0.5 ? transition(2 * pos, params) : (2 - transition(2 * (1 - pos), params))) / 2;
        }
    });
};

Fx.Transitions = {

    linear: function (zero) {
        return zero;
    }

};

//<1.2compat>

Fx.Transitions = new Hash(Fx.Transitions);

//</1.2compat>

Fx.Transitions.extend = function (transitions) {
    for (var transition in transitions) Fx.Transitions[transition] = new Fx.Transition(transitions[transition]);
};

Fx.Transitions.extend({

    Pow: function (p, x) {
        return Math.pow(p, x && x[0] || 6);
    },

    Expo: function (p) {
        return Math.pow(2, 8 * (p - 1));
    },

    Circ: function (p) {
        return 1 - Math.sin(Math.acos(p));
    },

    Sine: function (p) {
        return 1 - Math.cos(p * Math.PI / 2);
    },

    Back: function (p, x) {
        x = x && x[0] || 1.618;
        return Math.pow(p, 2) * ((x + 1) * p - x);
    },

    Bounce: function (p) {
        var value;
        for (var a = 0, b = 1; 1; a += b, b /= 2) {
            if (p >= (7 - 4 * a) / 11) {
                value = b * b - Math.pow((11 - 6 * a - 11 * p) / 4, 2);
                break;
            }
        }
        return value;
    },

    Elastic: function (p, x) {
        return Math.pow(2, 10 * --p) * Math.cos(20 * p * Math.PI * (x && x[0] || 1) / 3);
    }

});

['Quad', 'Cubic', 'Quart', 'Quint'].each(function (transition, i) {
    Fx.Transitions[transition] = new Fx.Transition(function (p) {
        return Math.pow(p, i + 2);
    });
});


/*
 ---

 name: Request

 description: Powerful all purpose Request Class. Uses XMLHTTPRequest.

 license: MIT-style license.

 requires: [Object, Element, Chain, Events, Options, Browser]

 provides: Request

 ...
 */

(function () {

    var empty = function () {
        },
        progressSupport = ('onprogress' in new Browser.Request);

    var Request = this.Request = new Class({

        Implements: [Chain, Events, Options],

        options: {/*
         onRequest: function(){},
         onLoadstart: function(event, xhr){},
         onProgress: function(event, xhr){},
         onComplete: function(){},
         onCancel: function(){},
         onSuccess: function(responseText, responseXML){},
         onFailure: function(xhr){},
         onException: function(headerName, value){},
         onTimeout: function(){},
         user: '',
         password: '',*/
            url: '',
            data: '',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/javascript, text/html, application/xml, text/xml, */*'
            },
            async: true,
            format: false,
            method: 'post',
            link: 'ignore',
            isSuccess: null,
            emulation: true,
            urlEncoded: true,
            encoding: 'utf-8',
            evalScripts: false,
            evalResponse: false,
            timeout: 0,
            noCache: false
        },

        initialize: function (options) {
            this.xhr = new Browser.Request();
            this.setOptions(options);
            this.headers = this.options.headers;
        },

        onStateChange: function () {
            var xhr = this.xhr;
            if (xhr.readyState != 4 || !this.running) return;
            this.running = false;
            this.status = 0;
            Function.attempt(function () {
                var status = xhr.status;
                this.status = (status == 1223) ? 204 : status;
            }.bind(this));
            xhr.onreadystatechange = empty;
            if (progressSupport) xhr.onprogress = xhr.onloadstart = empty;
            clearTimeout(this.timer);

            this.response = {text: this.xhr.responseText || '', xml: this.xhr.responseXML};
            if (this.options.isSuccess.call(this, this.status))
                this.success(this.response.text, this.response.xml);
            else
                this.failure();
        },

        isSuccess: function () {
            var status = this.status;
            return (status >= 200 && status < 300);
        },

        isRunning: function () {
            return !!this.running;
        },

        processScripts: function (text) {
            if (this.options.evalResponse || (/(ecma|java)script/).test(this.getHeader('Content-type'))) return Browser.exec(text);
            return text.stripScripts(this.options.evalScripts);
        },

        success: function (text, xml) {
            this.onSuccess(this.processScripts(text), xml);
        },

        onSuccess: function () {
            this.fireEvent('complete', arguments).fireEvent('success', arguments).callChain();
        },

        failure: function () {
            this.onFailure();
        },

        onFailure: function () {
            this.fireEvent('complete').fireEvent('failure', this.xhr);
        },

        loadstart: function (event) {
            this.fireEvent('loadstart', [event, this.xhr]);
        },

        progress: function (event) {
            this.fireEvent('progress', [event, this.xhr]);
        },

        timeout: function () {
            this.fireEvent('timeout', this.xhr);
        },

        setHeader: function (name, value) {
            this.headers[name] = value;
            return this;
        },

        getHeader: function (name) {
            return Function.attempt(function () {
                return this.xhr.getResponseHeader(name);
            }.bind(this));
        },

        check: function () {
            if (!this.running) return true;
            switch (this.options.link) {
                case 'cancel':
                    this.cancel();
                    return true;
                case 'chain':
                    this.chain(this.caller.pass(arguments, this));
                    return false;
            }
            return false;
        },

        send: function (options) {
            if (!this.check(options)) return this;

            this.options.isSuccess = this.options.isSuccess || this.isSuccess;
            this.running = true;

            var type = typeOf(options);
            if (type == 'string' || type == 'element') options = {data: options};

            var old = this.options;
            options = Object.append({data: old.data, url: old.url, method: old.method}, options);
            var data = options.data, url = String(options.url), method = options.method.toLowerCase();

            switch (typeOf(data)) {
                case 'element':
                    data = document.id(data).toQueryString();
                    break;
                case 'object':
                case 'hash':
                    data = Object.toQueryString(data);
            }

            if (this.options.format) {
                var format = 'format=' + this.options.format;
                data = (data) ? format + '&' + data : format;
            }

            if (this.options.emulation && !['get', 'post'].contains(method)) {
                var _method = '_method=' + method;
                data = (data) ? _method + '&' + data : _method;
                method = 'post';
            }

            if (this.options.urlEncoded && ['post', 'put'].contains(method)) {
                var encoding = (this.options.encoding) ? '; charset=' + this.options.encoding : '';
                this.headers['Content-type'] = 'application/x-www-form-urlencoded' + encoding;
            }

            if (!url) url = document.location.pathname;

            var trimPosition = url.lastIndexOf('/');
            if (trimPosition > -1 && (trimPosition = url.indexOf('#')) > -1) url = url.substr(0, trimPosition);

            if (this.options.noCache)
                url += (url.contains('?') ? '&' : '?') + String.uniqueID();

            if (data && method == 'get') {
                url += (url.contains('?') ? '&' : '?') + data;
                data = null;
            }

            var xhr = this.xhr;
            if (progressSupport) {
                xhr.onloadstart = this.loadstart.bind(this);
                xhr.onprogress = this.progress.bind(this);
            }

            xhr.open(method.toUpperCase(), url, this.options.async, this.options.user, this.options.password);
            if (this.options.user && 'withCredentials' in xhr) xhr.withCredentials = true;

            xhr.onreadystatechange = this.onStateChange.bind(this);

            Object.each(this.headers, function (value, key) {
                try {
                    xhr.setRequestHeader(key, value);
                } catch (e) {
                    this.fireEvent('exception', [key, value]);
                }
            }, this);

            this.fireEvent('request');
            xhr.send(data);
            if (!this.options.async) this.onStateChange();
            else if (this.options.timeout) this.timer = this.timeout.delay(this.options.timeout, this);
            return this;
        },

        cancel: function () {
            if (!this.running) return this;
            this.running = false;
            var xhr = this.xhr;
            xhr.abort();
            clearTimeout(this.timer);
            xhr.onreadystatechange = empty;
            if (progressSupport) xhr.onprogress = xhr.onloadstart = empty;
            this.xhr = new Browser.Request();
            this.fireEvent('cancel');
            return this;
        }

    });

    var methods = {};
    ['get', 'post', 'put', 'delete', 'GET', 'POST', 'PUT', 'DELETE'].each(function (method) {
        methods[method] = function (data) {
            var object = {
                method: method
            };
            if (data != null) object.data = data;
            return this.send(object);
        };
    });

    Request.implement(methods);

    Element.Properties.send = {

        set: function (options) {
            var send = this.get('send').cancel();
            send.setOptions(options);
            return this;
        },

        get: function () {
            var send = this.retrieve('send');
            if (!send) {
                send = new Request({
                    data: this, link: 'cancel', method: this.get('method') || 'post', url: this.get('action')
                });
                this.store('send', send);
            }
            return send;
        }

    };

    Element.implement({

        send: function (url) {
            var sender = this.get('send');
            sender.send({data: this, url: url || sender.options.url});
            return this;
        }

    });

})();


/*
 ---

 name: Request.HTML

 description: Extends the basic Request Class with additional methods for interacting with HTML responses.

 license: MIT-style license.

 requires: [Element, Request]

 provides: Request.HTML

 ...
 */

Request.HTML = new Class({

    Extends: Request,

    options: {
        update: false,
        append: false,
        evalScripts: true,
        filter: false,
        headers: {
            Accept: 'text/html, application/xml, text/xml, */*'
        }
    },

    success: function (text) {
        var options = this.options, response = this.response;

        response.html = text.stripScripts(function (script) {
            response.javascript = script;
        });

        var match = response.html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
        if (match) response.html = match[1];
        var temp = new Element('div').set('html', response.html);

        response.tree = temp.childNodes;
        response.elements = temp.getElements(options.filter || '*');

        if (options.filter) response.tree = response.elements;
        if (options.update) {
            var update = document.id(options.update).empty();
            if (options.filter) update.adopt(response.elements);
            else update.set('html', response.html);
        } else if (options.append) {
            var append = document.id(options.append);
            if (options.filter) response.elements.reverse().inject(append);
            else append.adopt(temp.getChildren());
        }
        if (options.evalScripts) Browser.exec(response.javascript);

        this.onSuccess(response.tree, response.elements, response.html, response.javascript);
    }

});

Element.Properties.load = {

    set: function (options) {
        var load = this.get('load').cancel();
        load.setOptions(options);
        return this;
    },

    get: function () {
        var load = this.retrieve('load');
        if (!load) {
            load = new Request.HTML({data: this, link: 'cancel', update: this, method: 'get'});
            this.store('load', load);
        }
        return load;
    }

};

Element.implement({

    load: function () {
        this.get('load').send(Array.link(arguments, {data: Type.isObject, url: Type.isString}));
        return this;
    }

});


/*
 ---

 name: JSON

 description: JSON encoder and decoder.

 license: MIT-style license.

 SeeAlso: <http://www.json.org/>

 requires: [Array, String, Number, Function]

 provides: JSON

 ...
 */

if (typeof JSON == 'undefined') this.JSON = {};

//<1.2compat>

JSON = new Hash({
    stringify: JSON.stringify,
    parse: JSON.parse
});

//</1.2compat>

(function () {

    var special = {'\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"': '\\"', '\\': '\\\\'};

    var escape = function (chr) {
        return special[chr] || '\\u' + ('0000' + chr.charCodeAt(0).toString(16)).slice(-4);
    };

    JSON.validate = function (string) {
        string = string.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
            replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
            replace(/(?:^|:|,)(?:\s*\[)+/g, '');

        return (/^[\],:{}\s]*$/).test(string);
    };

    JSON.encode = JSON.stringify ? function (obj) {
        return JSON.stringify(obj);
    } : function (obj) {
        if (obj && obj.toJSON) obj = obj.toJSON();

        switch (typeOf(obj)) {
            case 'string':
                return '"' + obj.replace(/[\x00-\x1f\\"]/g, escape) + '"';
            case 'array':
                return '[' + obj.map(JSON.encode).clean() + ']';
            case 'object':
            case 'hash':
                var string = [];
                Object.each(obj, function (value, key) {
                    var json = JSON.encode(value);
                    if (json) string.push(JSON.encode(key) + ':' + json);
                });
                return '{' + string + '}';
            case 'number':
            case 'boolean':
                return '' + obj;
            case 'null':
                return 'null';
        }

        return null;
    };

    JSON.decode = function (string, secure) {
        if (!string || typeOf(string) != 'string') return null;

        if (secure || JSON.secure) {
            if (JSON.parse) return JSON.parse(string);
            if (!JSON.validate(string)) throw new Error('JSON could not decode the input; security is enabled and the value is not secure.');
        }

        return eval('(' + string + ')');
    };

})();


/*
 ---

 name: Request.JSON

 description: Extends the basic Request Class with additional methods for sending and receiving JSON data.

 license: MIT-style license.

 requires: [Request, JSON]

 provides: Request.JSON

 ...
 */

Request.JSON = new Class({

    Extends: Request,

    options: {
        /*onError: function(text, error){},*/
        secure: true
    },

    initialize: function (options) {
        this.parent(options);
        Object.append(this.headers, {
            'Accept': 'application/json',
            'X-Request': 'JSON'
        });
    },

    success: function (text) {
        var json;
        try {
            json = this.response.json = JSON.decode(text, this.options.secure);
        } catch (error) {
            this.fireEvent('error', [text, error]);
            return;
        }
        if (json == null) this.onFailure();
        else this.onSuccess(json, text);
    }

});


/*
 ---

 name: Cookie

 description: Class for creating, reading, and deleting browser Cookies.

 license: MIT-style license.

 credits:
 - Based on the functions by Peter-Paul Koch (http://quirksmode.org).

 requires: [Options, Browser]

 provides: Cookie

 ...
 */

var Cookie = new Class({

    Implements: Options,

    options: {
        path: '/',
        domain: false,
        duration: false,
        secure: false,
        document: document,
        encode: true
    },

    initialize: function (key, options) {
        this.key = key;
        this.setOptions(options);
    },

    write: function (value) {
        if (this.options.encode) value = encodeURIComponent(value);
        if (this.options.domain) value += '; domain=' + this.options.domain;
        if (this.options.path) value += '; path=' + this.options.path;
        if (this.options.duration) {
            var date = new Date();
            date.setTime(date.getTime() + this.options.duration * 24 * 60 * 60 * 1000);
            value += '; expires=' + date.toGMTString();
        }
        if (this.options.secure) value += '; secure';
        this.options.document.cookie = this.key + '=' + value;
        return this;
    },

    read: function () {
        var value = this.options.document.cookie.match('(?:^|;)\\s*' + this.key.escapeRegExp() + '=([^;]*)');
        return (value) ? decodeURIComponent(value[1]) : null;
    },

    dispose: function () {
        new Cookie(this.key, Object.merge({}, this.options, {duration: -1})).write('');
        return this;
    }

});

Cookie.write = function (key, value, options) {
    return new Cookie(key, options).write(value);
};

Cookie.read = function (key) {
    return new Cookie(key).read();
};

Cookie.dispose = function (key, options) {
    return new Cookie(key, options).dispose();
};


/*
 ---

 name: DOMReady

 description: Contains the custom event domready.

 license: MIT-style license.

 requires: [Browser, Element, Element.Event]

 provides: [DOMReady, DomReady]

 ...
 */

(function (window, document) {

    var ready,
        loaded,
        checks = [],
        shouldPoll,
        timer,
        testElement = document.createElement('div');

    var domready = function () {
        clearTimeout(timer);
        if (ready) return;
        Browser.loaded = ready = true;
        document.removeListener('DOMContentLoaded', domready).removeListener('readystatechange', check);

        document.fireEvent('domready');
        window.fireEvent('domready');
    };

    var check = function () {
        for (var i = checks.length; i--;) if (checks[i]()) {
            domready();
            return true;
        }
        return false;
    };

    var poll = function () {
        clearTimeout(timer);
        if (!check()) timer = setTimeout(poll, 10);
    };

    document.addListener('DOMContentLoaded', domready);

    /*<ltIE8>*/
// doScroll technique by Diego Perini http://javascript.nwbox.com/IEContentLoaded/
// testElement.doScroll() throws when the DOM is not ready, only in the top window
    var doScrollWorks = function () {
        try {
            testElement.doScroll();
            return true;
        } catch (e) {
        }
        return false;
    };
// If doScroll works already, it can't be used to determine domready
//   e.g. in an iframe
    if (testElement.doScroll && !doScrollWorks()) {
        checks.push(doScrollWorks);
        shouldPoll = true;
    }
    /*</ltIE8>*/

    if (document.readyState) checks.push(function () {
        var state = document.readyState;
        return (state == 'loaded' || state == 'complete');
    });

    if ('onreadystatechange' in document) document.addListener('readystatechange', check);
    else shouldPoll = true;

    if (shouldPoll) poll();

    Element.Events.domready = {
        onAdd: function (fn) {
            if (ready) fn.call(this);
        }
    };

// Make sure that domready fires before load
    Element.Events.load = {
        base: 'load',
        onAdd: function (fn) {
            if (loaded && this == window) fn.call(this);
        },
        condition: function () {
            if (this == window) {
                domready();
                delete Element.Events.load;
            }
            return true;
        }
    };

// This is based on the custom load event
    window.addEvent('load', function () {
        loaded = true;
    });

})(window, document);


/*
 ---

 name: Swiff

 description: Wrapper for embedding SWF movies. Supports External Interface Communication.

 license: MIT-style license.

 credits:
 - Flash detection & Internet Explorer + Flash Player 9 fix inspired by SWFObject.

 requires: [Options, Object, Element]

 provides: Swiff

 ...
 */

(function () {

    var Swiff = this.Swiff = new Class({

        Implements: Options,

        options: {
            id: null,
            height: 1,
            width: 1,
            container: null,
            properties: {},
            params: {
                quality: 'high',
                allowScriptAccess: 'always',
                wMode: 'window',
                swLiveConnect: true
            },
            callBacks: {},
            vars: {}
        },

        toElement: function () {
            return this.object;
        },

        initialize: function (path, options) {
            this.instance = 'Swiff_' + String.uniqueID();

            this.setOptions(options);
            options = this.options;
            var id = this.id = options.id || this.instance;
            var container = document.id(options.container);

            Swiff.CallBacks[this.instance] = {};

            var params = options.params, vars = options.vars, callBacks = options.callBacks;
            var properties = Object.append({height: options.height, width: options.width}, options.properties);

            var self = this;

            for (var callBack in callBacks) {
                Swiff.CallBacks[this.instance][callBack] = (function (option) {
                    return function () {
                        return option.apply(self.object, arguments);
                    };
                })(callBacks[callBack]);
                vars[callBack] = 'Swiff.CallBacks.' + this.instance + '.' + callBack;
            }

            params.flashVars = Object.toQueryString(vars);
            if (Browser.ie) {
                properties.classid = 'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000';
                params.movie = path;
            } else {
                properties.type = 'application/x-shockwave-flash';
            }
            properties.data = path;

            var build = '<object id="' + id + '"';
            for (var property in properties) build += ' ' + property + '="' + properties[property] + '"';
            build += '>';
            for (var param in params) {
                if (params[param]) build += '<param name="' + param + '" value="' + params[param] + '" />';
            }
            build += '</object>';
            this.object = ((container) ? container.empty() : new Element('div')).set('html', build).firstChild;
        },

        replaces: function (element) {
            element = document.id(element, true);
            element.parentNode.replaceChild(this.toElement(), element);
            return this;
        },

        inject: function (element) {
            document.id(element, true).appendChild(this.toElement());
            return this;
        },

        remote: function () {
            return Swiff.remote.apply(Swiff, [this.toElement()].append(arguments));
        }

    });

    Swiff.CallBacks = {};

    Swiff.remote = function (obj, fn) {
        var rs = obj.CallFunction('<invoke name="' + fn + '" returntype="javascript">' + __flash__argumentsToXML(arguments, 2) + '</invoke>');
        return eval(rs);
    };

})();


//MooTools More, <http://mootools.net/more>. Copyright (c) 2006-2009 Aaron Newton <http://clientcide.com/>, Valerio Proietti <http://mad4milk.net> & the MooTools team <http://mootools.net/developers>, MIT Style License.

MooTools.More = {
    'version': '1.2.3.1'
};

/*
 Script: Fx.Scroll.js
 Effect to smoothly scroll any element, including the window.

 License:
 MIT-style license.

 Authors:
 Valerio Proietti
 */

Fx.Scroll = new Class({

    Extends: Fx,

    options: {
        offset: {x: 0, y: 0},
        wheelStops: true
    },

    initialize: function (element, options) {
        this.element = this.subject = document.id(element);
        this.parent(options);
        var cancel = this.cancel.bind(this, false);

        if ($type(this.element) != 'element') this.element = document.id(this.element.getDocument().body);

        var stopper = this.element;

        if (this.options.wheelStops) {
            this.addEvent('start', function () {
                stopper.addEvent('mousewheel', cancel);
            }, true);
            this.addEvent('complete', function () {
                stopper.removeEvent('mousewheel', cancel);
            }, true);
        }
    },

    set: function () {
        var now = Array.flatten(arguments);
        this.element.scrollTo(now[0], now[1]);
    },

    compute: function (from, to, delta) {
        return [0, 1].map(function (i) {
            return Fx.compute(from[i], to[i], delta);
        });
    },

    start: function (x, y) {
        if (!this.check(x, y)) return this;
        var offsetSize = this.element.getSize(), scrollSize = this.element.getScrollSize();
        var scroll = this.element.getScroll(), values = {x: x, y: y};
        for (var z in values) {
            var max = scrollSize[z] - offsetSize[z];
            if ($chk(values[z])) values[z] = ($type(values[z]) == 'number') ? values[z].limit(0, max) : max;
            else values[z] = scroll[z];
            values[z] += this.options.offset[z];
        }
        return this.parent([scroll.x, scroll.y], [values.x, values.y]);
    },

    toTop: function () {
        return this.start(false, 0);
    },

    toLeft: function () {
        return this.start(0, false);
    },

    toRight: function () {
        return this.start('right', false);
    },

    toBottom: function () {
        return this.start(false, 'bottom');
    },

    toElement: function (el) {
        var position = document.id(el).getPosition(this.element);
        return this.start(position.x, position.y);
    },

    scrollIntoView: function (el, axes, offset) {
        axes = axes ? $splat(axes) : ['x', 'y'];
        var to = {};
        el = document.id(el);
        var pos = el.getPosition(this.element);
        var size = el.getSize();
        var scroll = this.element.getScroll();
        var containerSize = this.element.getSize();
        var edge = {
            x: pos.x + size.x,
            y: pos.y + size.y
        };
        ['x', 'y'].each(function (axis) {
            if (axes.contains(axis)) {
                if (edge[axis] > scroll[axis] + containerSize[axis]) to[axis] = edge[axis] - containerSize[axis];
                if (pos[axis] < scroll[axis]) to[axis] = pos[axis];
            }
            if (to[axis] == null) to[axis] = scroll[axis];
            if (offset && offset[axis]) to[axis] = to[axis] + offset[axis];
        }, this);
        if (to.x != scroll.x || to.y != scroll.y) this.start(to.x, to.y);
        return this;
    }

});

// MooTools: the javascript framework.
// Load this file's selection again by visiting: http://mootools.net/more/f7005197184c1ad698fa1b435a9aecc0 
// Or build this file again with packager using: packager build More/Drag More/Drag.Move
/*
 ---

 script: More.js

 name: More

 description: MooTools More

 license: MIT-style license

 authors:
 - Guillermo Rauch
 - Thomas Aylott
 - Scott Kyle
 - Arian Stolwijk
 - Tim Wienk
 - Christoph Pojer
 - Aaron Newton
 - Jacob Thornton

 requires:
 - Core/MooTools

 provides: [MooTools.More]

 ...
 */

MooTools.More = {
    'version': '1.4.0.1',
    'build': 'a4244edf2aa97ac8a196fc96082dd35af1abab87'
};


/*
 ---

 script: Drag.js

 name: Drag

 description: The base Drag Class. Can be used to drag and resize Elements using mouse events.

 license: MIT-style license

 authors:
 - Valerio Proietti
 - Tom Occhinno
 - Jan Kassens

 requires:
 - Core/Events
 - Core/Options
 - Core/Element.Event
 - Core/Element.Style
 - Core/Element.Dimensions
 - /MooTools.More

 provides: [Drag]
 ...

 */

var Drag = new Class({

    Implements: [Events, Options],

    options: {/*
     onBeforeStart: function(thisElement){},
     onStart: function(thisElement, event){},
     onSnap: function(thisElement){},
     onDrag: function(thisElement, event){},
     onCancel: function(thisElement){},
     onComplete: function(thisElement, event){},*/
        snap: 6,
        unit: 'px',
        grid: false,
        style: true,
        limit: false,
        handle: false,
        invert: false,
        preventDefault: false,
        stopPropagation: false,
        modifiers: {x: 'left', y: 'top'}
    },

    initialize: function () {
        var params = Array.link(arguments, {
            'options': Type.isObject,
            'element': function (obj) {
                return obj != null;
            }
        });

        this.element = document.id(params.element);
        this.document = this.element.getDocument();
        this.setOptions(params.options || {});
        var htype = typeOf(this.options.handle);
        this.handles = ((htype == 'array' || htype == 'collection') ? $$(this.options.handle) : document.id(this.options.handle)) || this.element;
        this.mouse = {'now': {}, 'pos': {}};
        this.value = {'start': {}, 'now': {}};

        this.selection = (Browser.ie) ? 'selectstart' : 'mousedown';


        if (Browser.ie && !Drag.ondragstartFixed) {
            document.ondragstart = Function.from(false);
            Drag.ondragstartFixed = true;
        }

        this.bound = {
            start: this.start.bind(this),
            check: this.check.bind(this),
            drag: this.drag.bind(this),
            stop: this.stop.bind(this),
            cancel: this.cancel.bind(this),
            eventStop: Function.from(false)
        };
        this.attach();
    },

    attach: function () {
        this.handles.addEvent('mousedown', this.bound.start);
        return this;
    },

    detach: function () {
        this.handles.removeEvent('mousedown', this.bound.start);
        return this;
    },

    start: function (event) {
        var options = this.options;

        if (event.rightClick) return;

        if (options.preventDefault) event.preventDefault();
        if (options.stopPropagation) event.stopPropagation();
        this.mouse.start = event.page;

        this.fireEvent('beforeStart', this.element);

        var limit = options.limit;
        this.limit = {x: [], y: []};

        var z, coordinates;
        for (z in options.modifiers) {
            if (!options.modifiers[z]) continue;

            var style = this.element.getStyle(options.modifiers[z]);

            // Some browsers (IE and Opera) don't always return pixels.
            if (style && !style.match(/px$/)) {
                if (!coordinates) coordinates = this.element.getCoordinates(this.element.getOffsetParent());
                style = coordinates[options.modifiers[z]];
            }

            if (options.style) this.value.now[z] = (style || 0).toInt();
            else this.value.now[z] = this.element[options.modifiers[z]];

            if (options.invert) this.value.now[z] *= -1;

            this.mouse.pos[z] = event.page[z] - this.value.now[z];

            if (limit && limit[z]) {
                var i = 2;
                while (i--) {
                    var limitZI = limit[z][i];
                    if (limitZI || limitZI === 0) this.limit[z][i] = (typeof limitZI == 'function') ? limitZI() : limitZI;
                }
            }
        }

        if (typeOf(this.options.grid) == 'number') this.options.grid = {
            x: this.options.grid,
            y: this.options.grid
        };

        var events = {
            mousemove: this.bound.check,
            mouseup: this.bound.cancel
        };
        events[this.selection] = this.bound.eventStop;
        this.document.addEvents(events);
    },

    check: function (event) {
        if (this.options.preventDefault) event.preventDefault();
        var distance = Math.round(Math.sqrt(Math.pow(event.page.x - this.mouse.start.x, 2) + Math.pow(event.page.y - this.mouse.start.y, 2)));
        if (distance > this.options.snap) {
            this.cancel();
            this.document.addEvents({
                mousemove: this.bound.drag,
                mouseup: this.bound.stop
            });
            this.fireEvent('start', [this.element, event]).fireEvent('snap', this.element);
        }
    },

    drag: function (event) {
        var options = this.options;

        if (options.preventDefault) event.preventDefault();
        this.mouse.now = event.page;

        for (var z in options.modifiers) {
            if (!options.modifiers[z]) continue;
            this.value.now[z] = this.mouse.now[z] - this.mouse.pos[z];

            if (options.invert) this.value.now[z] *= -1;

            if (options.limit && this.limit[z]) {
                if ((this.limit[z][1] || this.limit[z][1] === 0) && (this.value.now[z] > this.limit[z][1])) {
                    this.value.now[z] = this.limit[z][1];
                } else if ((this.limit[z][0] || this.limit[z][0] === 0) && (this.value.now[z] < this.limit[z][0])) {
                    this.value.now[z] = this.limit[z][0];
                }
            }

            if (options.grid[z]) this.value.now[z] -= ((this.value.now[z] - (this.limit[z][0] || 0)) % options.grid[z]);

            if (options.style) this.element.setStyle(options.modifiers[z], this.value.now[z] + options.unit);
            else this.element[options.modifiers[z]] = this.value.now[z];
        }

        this.fireEvent('drag', [this.element, event]);
    },

    cancel: function (event) {
        this.document.removeEvents({
            mousemove: this.bound.check,
            mouseup: this.bound.cancel
        });
        if (event) {
            this.document.removeEvent(this.selection, this.bound.eventStop);
            this.fireEvent('cancel', this.element);
        }
    },

    stop: function (event) {
        var events = {
            mousemove: this.bound.drag,
            mouseup: this.bound.stop
        };
        events[this.selection] = this.bound.eventStop;
        this.document.removeEvents(events);
        if (event) this.fireEvent('complete', [this.element, event]);
    }

});

Element.implement({

    makeResizable: function (options) {
        var drag = new Drag(this, Object.merge({
            modifiers: {
                x: 'width',
                y: 'height'
            }
        }, options));

        this.store('resizer', drag);
        return drag.addEvent('drag', function () {
            this.fireEvent('resize', drag);
        }.bind(this));
    }

});


/*
 ---

 script: Drag.Move.js

 name: Drag.Move

 description: A Drag extension that provides support for the constraining of draggables to containers and droppables.

 license: MIT-style license

 authors:
 - Valerio Proietti
 - Tom Occhinno
 - Jan Kassens
 - Aaron Newton
 - Scott Kyle

 requires:
 - Core/Element.Dimensions
 - /Drag

 provides: [Drag.Move]

 ...
 */

Drag.Move = new Class({

    Extends: Drag,

    options: {/*
     onEnter: function(thisElement, overed){},
     onLeave: function(thisElement, overed){},
     onDrop: function(thisElement, overed, event){},*/
        droppables: [],
        container: false,
        precalculate: false,
        includeMargins: true,
        checkDroppables: true
    },

    initialize: function (element, options) {
        this.parent(element, options);
        element = this.element;

        this.droppables = $$(this.options.droppables);
        this.container = document.id(this.options.container);

        if (this.container && typeOf(this.container) != 'element')
            this.container = document.id(this.container.getDocument().body);

        if (this.options.style) {
            if (this.options.modifiers.x == 'left' && this.options.modifiers.y == 'top') {

                var parent = element.getOffsetParent(),
                    styles = element.getStyles('left', 'top');
                if (parent && (styles.left == 'auto' || styles.top == 'auto')) {
                    element.setPosition(element.getPosition(parent));
                }
            }

            if (element.getStyle('position') == 'static') element.setStyle('position', 'absolute');
        }

        this.addEvent('start', this.checkDroppables, true);
        this.overed = null;
    },

    start: function (event) {
        if (this.container) this.options.limit = this.calculateLimit();

        if (this.options.precalculate) {
            this.positions = this.droppables.map(function (el) {
                return el.getCoordinates();
            });
        }

        this.parent(event);
    },

    calculateLimit: function () {
        var element = this.element,
            container = this.container,

            offsetParent = document.id(element.getOffsetParent()) || document.body,
            containerCoordinates = container.getCoordinates(offsetParent),
            elementMargin = {},
            elementBorder = {},
            containerMargin = {},
            containerBorder = {},
            offsetParentPadding = {};

        ['top', 'right', 'bottom', 'left'].each(function (pad) {
            elementMargin[pad] = element.getStyle('margin-' + pad).toInt();
            elementBorder[pad] = element.getStyle('border-' + pad).toInt();
            containerMargin[pad] = container.getStyle('margin-' + pad).toInt();
            containerBorder[pad] = container.getStyle('border-' + pad).toInt();
            offsetParentPadding[pad] = offsetParent.getStyle('padding-' + pad).toInt();
        }, this);

        var width = element.offsetWidth + elementMargin.left + elementMargin.right,
            height = element.offsetHeight + elementMargin.top + elementMargin.bottom,
            left = 0,
            top = 0,
            right = containerCoordinates.right - containerBorder.right - width,
            bottom = containerCoordinates.bottom - containerBorder.bottom - height;

        if (this.options.includeMargins) {
            left += elementMargin.left;
            top += elementMargin.top;
        } else {
            right += elementMargin.right;
            bottom += elementMargin.bottom;
        }

        if (element.getStyle('position') == 'relative') {
            var coords = element.getCoordinates(offsetParent);
            coords.left -= element.getStyle('left').toInt();
            coords.top -= element.getStyle('top').toInt();

            left -= coords.left;
            top -= coords.top;
            if (container.getStyle('position') != 'relative') {
                left += containerBorder.left;
                top += containerBorder.top;
            }
            right += elementMargin.left - coords.left;
            bottom += elementMargin.top - coords.top;

            if (container != offsetParent) {
                left += containerMargin.left + offsetParentPadding.left;
                top += ((Browser.ie6 || Browser.ie7) ? 0 : containerMargin.top) + offsetParentPadding.top;
            }
        } else {
            left -= elementMargin.left;
            top -= elementMargin.top;
            if (container != offsetParent) {
                left += containerCoordinates.left + containerBorder.left;
                top += containerCoordinates.top + containerBorder.top;
            }
        }

        return {
            x: [left, right],
            y: [top, bottom]
        };
    },

    getDroppableCoordinates: function (element) {
        var position = element.getCoordinates();
        if (element.getStyle('position') == 'fixed') {
            var scroll = window.getScroll();
            position.left += scroll.x;
            position.right += scroll.x;
            position.top += scroll.y;
            position.bottom += scroll.y;
        }
        return position;
    },

    checkDroppables: function () {
        var overed = this.droppables.filter(function (el, i) {
            el = this.positions ? this.positions[i] : this.getDroppableCoordinates(el);
            var now = this.mouse.now;
            return (now.x > el.left && now.x < el.right && now.y < el.bottom && now.y > el.top);
        }, this).getLast();

        if (this.overed != overed) {
            if (this.overed) this.fireEvent('leave', [this.element, this.overed]);
            if (overed) this.fireEvent('enter', [this.element, overed]);
            this.overed = overed;
        }
    },

    drag: function (event) {
        this.parent(event);
        if (this.options.checkDroppables && this.droppables.length) this.checkDroppables();
    },

    stop: function (event) {
        this.checkDroppables();
        this.fireEvent('drop', [this.element, this.overed, event]);
        this.overed = null;
        return this.parent(event);
    }

});

Element.implement({

    makeDraggable: function (options) {
        var drag = new Drag.Move(this, options);
        this.store('dragger', drag);
        return drag;
    }

});
/*
 ---

 script: Sortables.js

 name: Sortables

 description: Class for creating a drag and drop sorting interface for lists of items.

 license: MIT-style license

 authors:
 - Tom Occhino

 requires:
 - Core/Fx.Morph
 - /Drag.Move

 provides: [Sortables]

 ...
 */

var Sortables = new Class({

    Implements: [Events, Options],

    options: {/*
     onSort: function(element, clone){},
     onStart: function(element, clone){},
     onComplete: function(element){},*/
        opacity: 1,
        clone: false,
        revert: false,
        handle: false,
        dragOptions: {}
    },

    initialize: function (lists, options) {
        this.setOptions(options);

        this.elements = [];
        this.lists = [];
        this.idle = true;

        this.addLists($$(document.id(lists) || lists));

        if (!this.options.clone) this.options.revert = false;
        if (this.options.revert) this.effect = new Fx.Morph(null, Object.merge({
            duration: 250,
            link: 'cancel'
        }, this.options.revert));
    },

    attach: function () {
        this.addLists(this.lists);
        return this;
    },

    detach: function () {
        this.lists = this.removeLists(this.lists);
        return this;
    },

    addItems: function () {
        Array.flatten(arguments).each(function (element) {
            this.elements.push(element);
            var start = element.retrieve('sortables:start', function (event) {
                this.start.call(this, event, element);
            }.bind(this));
            (this.options.handle ? element.getElement(this.options.handle) || element : element).addEvent('mousedown', start);
        }, this);
        return this;
    },

    addLists: function () {
        Array.flatten(arguments).each(function (list) {
            this.lists.include(list);
            this.addItems(list.getChildren());
        }, this);
        return this;
    },

    removeItems: function () {
        return $$(Array.flatten(arguments).map(function (element) {
            this.elements.erase(element);
            var start = element.retrieve('sortables:start');
            (this.options.handle ? element.getElement(this.options.handle) || element : element).removeEvent('mousedown', start);

            return element;
        }, this));
    },

    removeLists: function () {
        return $$(Array.flatten(arguments).map(function (list) {
            this.lists.erase(list);
            this.removeItems(list.getChildren());

            return list;
        }, this));
    },

    getClone: function (event, element) {
        if (!this.options.clone) return new Element(element.tagName).inject(document.body);
        if (typeOf(this.options.clone) == 'function') return this.options.clone.call(this, event, element, this.list);
        var clone = element.clone(true).setStyles({
            margin: 0,
            position: 'absolute',
            visibility: 'hidden',
            width: element.getStyle('width')
        }).addEvent('mousedown', function (event) {
            element.fireEvent('mousedown', event);
        });
        //prevent the duplicated radio inputs from unchecking the real one
        if (clone.get('html').test('radio')) {
            clone.getElements('input[type=radio]').each(function (input, i) {
                input.set('name', 'clone_' + i);
                if (input.get('checked')) element.getElements('input[type=radio]')[i].set('checked', true);
            });
        }

        return clone.inject(this.list).setPosition(element.getPosition(element.getOffsetParent()));
    },

    getDroppables: function () {
        var droppables = this.list.getChildren().erase(this.clone).erase(this.element);
        if (!this.options.constrain) droppables.append(this.lists).erase(this.list);
        return droppables;
    },

    insert: function (dragging, element) {
        var where = 'inside';
        if (this.lists.contains(element)) {
            this.list = element;
            this.drag.droppables = this.getDroppables();
        } else {
            where = this.element.getAllPrevious().contains(element) ? 'before' : 'after';
        }
        this.element.inject(element, where);
        this.fireEvent('sort', [this.element, this.clone]);
    },

    start: function (event, element) {
        if (
            !this.idle ||
                event.rightClick ||
                ['button', 'input', 'a', 'textarea'].contains(event.target.get('tag'))
            ) return;

        this.idle = false;
        this.element = element;
        this.opacity = element.getStyle('opacity');
        this.list = element.getParent();
        this.clone = this.getClone(event, element);

        this.drag = new Drag.Move(this.clone, Object.merge({

            droppables: this.getDroppables()
        }, this.options.dragOptions)).addEvents({
                onSnap: function () {
                    event.stop();
                    this.clone.setStyle('visibility', 'visible');
                    this.element.setStyle('opacity', this.options.opacity || 0);
                    this.fireEvent('start', [this.element, this.clone]);
                }.bind(this),
                onEnter: this.insert.bind(this),
                onCancel: this.end.bind(this),
                onComplete: this.end.bind(this)
            });

        this.clone.inject(this.element, 'before');
        this.drag.start(event);
    },

    end: function () {
        this.drag.detach();
        this.element.setStyle('opacity', this.opacity);
        if (this.effect) {
            var dim = this.element.getStyles('width', 'height'),
                clone = this.clone,
                pos = clone.computePosition(this.element.getPosition(this.clone.getOffsetParent()));

            var destroy = function () {
                this.removeEvent('cancel', destroy);
                clone.destroy();
            };

            this.effect.element = clone;
            this.effect.start({
                top: pos.top,
                left: pos.left,
                width: dim.width,
                height: dim.height,
                opacity: 0.25
            }).addEvent('cancel', destroy).chain(destroy);
        } else {
            this.clone.destroy();
        }
        this.reset();
    },

    reset: function () {
        this.idle = true;
        this.fireEvent('complete', this.element);
    },

    serialize: function () {
        var params = Array.link(arguments, {
            modifier: Type.isFunction,
            index: function (obj) {
                return obj != null;
            }
        });
        var serial = this.lists.map(function (list) {
            return list.getChildren().map(params.modifier || function (element) {
                return element.get('id');
            }, this);
        }, this);

        var index = params.index;
        if (this.lists.length == 1) index = 0;
        return (index || index === 0) && index >= 0 && index < this.lists.length ? serial[index] : serial;
    }

});


var Asset = {

    javascript: function (source, properties) {
        properties = $extend({
            onload: $empty,
            document: document,
            check: $lambda(true)
        }, properties);

        if (properties.onLoad) {
            properties.onload = properties.onLoad;
            delete properties.onLoad;
        }
        var script = new Element('script', {src: source, type: 'text/javascript'});

        var load = properties.onload.bind(script),
            check = properties.check,
            doc = properties.document;
        delete properties.onload;
        delete properties.check;
        delete properties.document;

        script.addEvents({
            load: load,
            readystatechange: function () {
                if (['loaded', 'complete'].contains(this.readyState)) load();
            }
        }).set(properties);

        if (Browser.Engine.webkit419) var checker = (function () {
            if (!$try(check)) return;
            $clear(checker);
            load();
        }).periodical(50);

        return script.inject(doc.head);
    },

    css: function (source, properties) {
        properties = properties || {};
        var onload = properties.onload || properties.onLoad;
        if (onload) {
            properties.events = properties.events || {};
            properties.events.load = onload;
            delete properties.onload;
            delete properties.onLoad;
        }
        return new Element('link', $merge({
            rel: 'stylesheet',
            media: 'screen',
            type: 'text/css',
            href: source
        }, properties)).inject(document.head);
    },

    image: function (source, properties) {
        properties = $merge({
            onload: $empty,
            onabort: $empty,
            onerror: $empty
        }, properties);
        var image = new Image();
        var element = document.id(image) || new Element('img');
        ['load', 'abort', 'error'].each(function (name) {
            var type = 'on' + name;
            var cap = name.capitalize();
            if (properties['on' + cap]) {
                properties[type] = properties['on' + cap];
                delete properties['on' + cap];
            }
            var event = properties[type];
            delete properties[type];
            image[type] = function () {
                if (!image) return;
                if (!element.parentNode) {
                    element.width = image.width;
                    element.height = image.height;
                }
                image = image.onload = image.onabort = image.onerror = null;
                event.delay(1, element, element);
                element.fireEvent(name, element, 1);
            };
        });
        image.src = element.src = source;
        if (image && image.complete) image.onload.delay(1);
        return element.set(properties);
    },

    images: function (sources, options) {
        options = $merge({
            onComplete: $empty,
            onProgress: $empty,
            onError: $empty,
            properties: {}
        }, options);
        sources = $splat(sources);
        var images = [];
        var counter = 0;
        return new Elements(sources.map(function (source, index) {
            return Asset.image(source, $extend(options.properties, {
                onload: function () {
                    options.onProgress.call(this, counter, index);
                    counter++;
                    if (counter == sources.length) options.onComplete();
                },
                onerror: function () {
                    options.onError.call(this, counter, index);
                    counter++;
                    if (counter == sources.length) options.onComplete();
                }
            }));
        }));
    }

};


//MooTools More, <http://mootools.net/more>. Copyright (c) 2006-2009 Aaron Newton <http://clientcide.com/>, Valerio Proietti <http://mad4milk.net> & the MooTools team <http://mootools.net/developers>, MIT Style License.

/*
 ---

 script: More.js

 name: More

 description: MooTools More

 license: MIT-style license

 requires:
 - Core/MooTools

 provides: [MooTools.More]

 ...
 */

MooTools.More = {
    'version': '1.2.5.1',
    'build': '254884f2b83651bf95260eed5c6cceb838e22d8e'
};


/*
 ---

 script: Color.js

 name: Color

 description: Class for creating and manipulating colors in JavaScript. Supports HSB -> RGB Conversions and vice versa.

 license: MIT-style license

 authors:
 - Valerio Proietti

 requires:
 - Core/Array
 - Core/String
 - Core/Number
 - Core/Hash
 - Core/Function
 - Core/$util

 provides: [Color]

 ...
 */

var Color = new Native({

    initialize: function (color, type) {
        if (arguments.length >= 3) {
            type = 'rgb';
            color = Array.slice(arguments, 0, 3);
        } else if (typeof color == 'string') {
            if (color.match(/rgb/)) color = color.rgbToHex().hexToRgb(true);
            else if (color.match(/hsb/)) color = color.hsbToRgb();
            else color = color.hexToRgb(true);
        }
        type = type || 'rgb';
        switch (type) {
            case 'hsb':
                var old = color;
                color = color.hsbToRgb();
                color.hsb = old;
                break;
            case 'hex':
                color = color.hexToRgb(true);
                break;
        }
        color.rgb = color.slice(0, 3);
        color.hsb = color.hsb || color.rgbToHsb();
        color.hex = color.rgbToHex();
        return $extend(color, this);
    }

});

Color.implement({

    mix: function () {
        var colors = Array.slice(arguments);
        var alpha = ($type(colors.getLast()) == 'number') ? colors.pop() : 50;
        var rgb = this.slice();
        colors.each(function (color) {
            color = new Color(color);
            for (var i = 0; i < 3; i++) rgb[i] = Math.round((rgb[i] / 100 * (100 - alpha)) + (color[i] / 100 * alpha));
        });
        return new Color(rgb, 'rgb');
    },

    invert: function () {
        return new Color(this.map(function (value) {
            return 255 - value;
        }));
    },

    setHue: function (value) {
        return new Color([value, this.hsb[1], this.hsb[2]], 'hsb');
    },

    setSaturation: function (percent) {
        return new Color([this.hsb[0], percent, this.hsb[2]], 'hsb');
    },

    setBrightness: function (percent) {
        return new Color([this.hsb[0], this.hsb[1], percent], 'hsb');
    }

});

var $RGB = function (r, g, b) {
    return new Color([r, g, b], 'rgb');
};

var $HSB = function (h, s, b) {
    return new Color([h, s, b], 'hsb');
};

var $HEX = function (hex) {
    return new Color(hex, 'hex');
};

Array.implement({

    rgbToHsb: function () {
        var red = this[0],
            green = this[1],
            blue = this[2],
            hue = 0;
        var max = Math.max(red, green, blue),
            min = Math.min(red, green, blue);
        var delta = max - min;
        var brightness = max / 255,
            saturation = (max != 0) ? delta / max : 0;
        if (saturation != 0) {
            var rr = (max - red) / delta;
            var gr = (max - green) / delta;
            var br = (max - blue) / delta;
            if (red == max) hue = br - gr;
            else if (green == max) hue = 2 + rr - br;
            else hue = 4 + gr - rr;
            hue /= 6;
            if (hue < 0) hue++;
        }
        return [Math.round(hue * 360), Math.round(saturation * 100), Math.round(brightness * 100)];
    },

    hsbToRgb: function () {
        var br = Math.round(this[2] / 100 * 255);
        if (this[1] == 0) {
            return [br, br, br];
        } else {
            var hue = this[0] % 360;
            var f = hue % 60;
            var p = Math.round((this[2] * (100 - this[1])) / 10000 * 255);
            var q = Math.round((this[2] * (6000 - this[1] * f)) / 600000 * 255);
            var t = Math.round((this[2] * (6000 - this[1] * (60 - f))) / 600000 * 255);
            switch (Math.floor(hue / 60)) {
                case 0:
                    return [br, t, p];
                case 1:
                    return [q, br, p];
                case 2:
                    return [p, br, t];
                case 3:
                    return [p, q, br];
                case 4:
                    return [t, p, br];
                case 5:
                    return [br, p, q];
            }
        }
        return false;
    }

});

String.implement({

    rgbToHsb: function () {
        var rgb = this.match(/\d{1,3}/g);
        return (rgb) ? rgb.rgbToHsb() : null;
    },

    hsbToRgb: function () {
        var hsb = this.match(/\d{1,3}/g);
        return (hsb) ? hsb.hsbToRgb() : null;
    }

});

/*
 ---

 script: More.js

 name: More

 description: MooTools More

 license: MIT-style license

 authors:
 - Guillermo Rauch
 - Thomas Aylott
 - Scott Kyle
 - Arian Stolwijk
 - Tim Wienk
 - Christoph Pojer
 - Aaron Newton
 - Jacob Thornton

 requires:
 - Core/MooTools

 provides: [MooTools.More]

 ...
 */

MooTools.More = {
    'version': '1.4.0.1',
    'build': 'a4244edf2aa97ac8a196fc96082dd35af1abab87'
};


/*
 ---

 script: String.QueryString.js

 name: String.QueryString

 description: Methods for dealing with URI query strings.

 license: MIT-style license

 authors:
 - Sebastian MarkbÃ¥ge
 - Aaron Newton
 - Lennart Pilon
 - Valerio Proietti

 requires:
 - Core/Array
 - Core/String
 - /MooTools.More

 provides: [String.QueryString]

 ...
 */

String.implement({

    parseQueryString: function (decodeKeys, decodeValues) {
        if (decodeKeys == null) decodeKeys = true;
        if (decodeValues == null) decodeValues = true;

        var vars = this.split(/[&;]/),
            object = {};
        if (!vars.length) return object;

        vars.each(function (val) {
            var index = val.indexOf('=') + 1,
                value = index ? val.substr(index) : '',
                keys = index ? val.substr(0, index - 1).match(/([^\]\[]+|(\B)(?=\]))/g) : [val],
                obj = object;
            if (!keys) return;
            if (decodeValues) value = decodeURIComponent(value);
            keys.each(function (key, i) {
                if (decodeKeys) key = decodeURIComponent(key);
                var current = obj[key];

                if (i < keys.length - 1) obj = obj[key] = current || {};
                else if (typeOf(current) == 'array') current.push(value);
                else obj[key] = current != null ? [current, value] : value;
            });
        });

        return object;
    },

    cleanQueryString: function (method) {
        return this.split('&').filter(function (val) {
            var index = val.indexOf('='),
                key = index < 0 ? '' : val.substr(0, index),
                value = val.substr(index + 1);

            return method ? method.call(null, key, value) : (value || value === 0);
        }).join('&');
    }

});


/*
 ---

 script: URI.js

 name: URI

 description: Provides methods useful in managing the window location and uris.

 license: MIT-style license

 authors:
 - Sebastian MarkbÃ¥ge
 - Aaron Newton

 requires:
 - Core/Object
 - Core/Class
 - Core/Class.Extras
 - Core/Element
 - /String.QueryString

 provides: [URI]

 ...
 */

(function () {

    var toString = function () {
        return this.get('value');
    };

    var URI = this.URI = new Class({

        Implements: Options,

        options: {
            /*base: false*/
        },

        regex: /^(?:(\w+):)?(?:\/\/(?:(?:([^:@\/]*):?([^:@\/]*))?@)?([^:\/?#]*)(?::(\d*))?)?(\.\.?$|(?:[^?#\/]*\/)*)([^?#]*)(?:\?([^#]*))?(?:#(.*))?/,
        parts: ['scheme', 'user', 'password', 'host', 'port', 'directory', 'file', 'query', 'fragment'],
        schemes: {http: 80, https: 443, ftp: 21, rtsp: 554, mms: 1755, file: 0},

        initialize: function (uri, options) {
            this.setOptions(options);
            var base = this.options.base || URI.base;
            if (!uri) uri = base;

            if (uri && uri.parsed) this.parsed = Object.clone(uri.parsed);
            else this.set('value', uri.href || uri.toString(), base ? new URI(base) : false);
        },

        parse: function (value, base) {
            var bits = value.match(this.regex);
            if (!bits) return false;
            bits.shift();
            return this.merge(bits.associate(this.parts), base);
        },

        merge: function (bits, base) {
            if ((!bits || !bits.scheme) && (!base || !base.scheme)) return false;
            if (base) {
                this.parts.every(function (part) {
                    if (bits[part]) return false;
                    bits[part] = base[part] || '';
                    return true;
                });
            }
            bits.port = bits.port || this.schemes[bits.scheme.toLowerCase()];
            bits.directory = bits.directory ? this.parseDirectory(bits.directory, base ? base.directory : '') : '/';
            return bits;
        },

        parseDirectory: function (directory, baseDirectory) {
            directory = (directory.substr(0, 1) == '/' ? '' : (baseDirectory || '/')) + directory;
            if (!directory.test(URI.regs.directoryDot)) return directory;
            var result = [];
            directory.replace(URI.regs.endSlash, '').split('/').each(function (dir) {
                if (dir == '..' && result.length > 0) result.pop();
                else if (dir != '.') result.push(dir);
            });
            return result.join('/') + '/';
        },

        combine: function (bits) {
            return bits.value || bits.scheme + '://' +
                (bits.user ? bits.user + (bits.password ? ':' + bits.password : '') + '@' : '') +
                (bits.host || '') + (bits.port && bits.port != this.schemes[bits.scheme] ? ':' + bits.port : '') +
                (bits.directory || '/') + (bits.file || '') +
                (bits.query ? '?' + bits.query : '') +
                (bits.fragment ? '#' + bits.fragment : '');
        },

        set: function (part, value, base) {
            if (part == 'value') {
                var scheme = value.match(URI.regs.scheme);
                if (scheme) scheme = scheme[1];
                if (scheme && this.schemes[scheme.toLowerCase()] == null) this.parsed = { scheme: scheme, value: value };
                else this.parsed = this.parse(value, (base || this).parsed) || (scheme ? { scheme: scheme, value: value } : { value: value });
            } else if (part == 'data') {
                this.setData(value);
            } else {
                this.parsed[part] = value;
            }
            return this;
        },

        get: function (part, base) {
            switch (part) {
                case 'value':
                    return this.combine(this.parsed, base ? base.parsed : false);
                case 'data' :
                    return this.getData();
            }
            return this.parsed[part] || '';
        },

        go: function () {
            document.location.href = this.toString();
        },

        toURI: function () {
            return this;
        },

        getData: function (key, part) {
            var qs = this.get(part || 'query');
            if (!(qs || qs === 0)) return key ? null : {};
            var obj = qs.parseQueryString();
            return key ? obj[key] : obj;
        },

        setData: function (values, merge, part) {
            if (typeof values == 'string') {
                var data = this.getData();
                data[arguments[0]] = arguments[1];
                values = data;
            } else if (merge) {
                values = Object.merge(this.getData(), values);
            }
            return this.set(part || 'query', Object.toQueryString(values));
        },

        clearData: function (part) {
            return this.set(part || 'query', '');
        },

        toString: toString,
        valueOf: toString

    });

    URI.regs = {
        endSlash: /\/$/,
        scheme: /^(\w+):/,
        directoryDot: /\.\/|\.$/
    };

    URI.base = new URI(Array.from(document.getElements('base[href]', true)).getLast(), {base: document.location});

    String.implement({

        toURI: function (options) {
            return new URI(this, options);
        }

    });

})();

/*
 ---

 script: Tips.js

 name: Tips

 description: Class for creating nice tips that follow the mouse cursor when hovering an element.

 license: MIT-style license

 authors:
 - Valerio Proietti
 - Christoph Pojer
 - Luis Merino

 requires:
 - Core/Options
 - Core/Events
 - Core/Element.Event
 - Core/Element.Style
 - Core/Element.Dimensions
 - /MooTools.More

 provides: [Tips]

 ...
 */

(function () {

    var read = function (option, element) {
        return (option) ? (typeOf(option) == 'function' ? option(element) : element.get(option)) : '';
    };

    this.Tips = new Class({

        Implements: [Events, Options],

        options: {/*
         id: null,
         onAttach: function(element){},
         onDetach: function(element){},
         onBound: function(coords){},*/
            onShow: function () {
                this.tip.setStyle('display', 'block');
            },
            onHide: function () {
                this.tip.setStyle('display', 'none');
            },
            title: 'title',
            text: function (element) {
                return element.get('rel') || element.get('href');
            },
            showDelay: 100,
            hideDelay: 100,
            className: 'tip-wrap',
            offset: {x: 16, y: 16},
            windowPadding: {x: 0, y: 0},
            fixed: false,
            waiAria: true
        },

        initialize: function () {
            var params = Array.link(arguments, {
                options: Type.isObject,
                elements: function (obj) {
                    return obj != null;
                }
            });
            this.setOptions(params.options);
            if (params.elements) this.attach(params.elements);
            this.container = new Element('div', {'class': 'tip'});

            if (this.options.id) {
                this.container.set('id', this.options.id);
                if (this.options.waiAria) this.attachWaiAria();
            }
        },

        toElement: function () {
            if (this.tip) return this.tip;

            this.tip = new Element('div', {
                'class': this.options.className,
                styles: {
                    position: 'absolute',
                    top: 0,
                    left: 0
                }
            }).adopt(
                    new Element('div', {'class': 'tip-top'}),
                    this.container,
                    new Element('div', {'class': 'tip-bottom'})
                );

            return this.tip;
        },

        attachWaiAria: function () {
            var id = this.options.id;
            this.container.set('role', 'tooltip');

            if (!this.waiAria) {
                this.waiAria = {
                    show: function (element) {
                        if (id) element.set('aria-describedby', id);
                        this.container.set('aria-hidden', 'false');
                    },
                    hide: function (element) {
                        if (id) element.erase('aria-describedby');
                        this.container.set('aria-hidden', 'true');
                    }
                };
            }
            this.addEvents(this.waiAria);
        },

        detachWaiAria: function () {
            if (this.waiAria) {
                this.container.erase('role');
                this.container.erase('aria-hidden');
                this.removeEvents(this.waiAria);
            }
        },

        attach: function (elements) {
            $$(elements).each(function (element) {
                var title = read(this.options.title, element),
                    text = read(this.options.text, element);

                element.set('title', '').store('tip:native', title).retrieve('tip:title', title);
                element.retrieve('tip:text', text);
                this.fireEvent('attach', [element]);

                var events = ['enter', 'leave'];
                if (!this.options.fixed) events.push('move');

                events.each(function (value) {
                    var event = element.retrieve('tip:' + value);
                    if (!event) event = function (event) {
                        this['element' + value.capitalize()].apply(this, [event, element]);
                    }.bind(this);

                    element.store('tip:' + value, event).addEvent('mouse' + value, event);
                }, this);
            }, this);

            return this;
        },

        detach: function (elements) {
            $$(elements).each(function (element) {
                ['enter', 'leave', 'move'].each(function (value) {
                    element.removeEvent('mouse' + value, element.retrieve('tip:' + value)).eliminate('tip:' + value);
                });

                this.fireEvent('detach', [element]);

                if (this.options.title == 'title') { // This is necessary to check if we can revert the title
                    var original = element.retrieve('tip:native');
                    if (original) element.set('title', original);
                }
            }, this);

            return this;
        },

        elementEnter: function (event, element) {
            clearTimeout(this.timer);
            this.timer = (function () {
                this.container.empty();

                ['title', 'text'].each(function (value) {
                    var content = element.retrieve('tip:' + value);
                    var div = this['_' + value + 'Element'] = new Element('div', {
                        'class': 'tip-' + value
                    }).inject(this.container);
                    if (content) this.fill(div, content);
                }, this);
                this.show(element);
                this.position((this.options.fixed) ? {page: element.getPosition()} : event, element);
            }).delay(this.options.showDelay, this);
        },

        elementLeave: function (event, element) {
            clearTimeout(this.timer);
            this.timer = this.hide.delay(this.options.hideDelay, this, element);
            this.fireForParent(event, element);
        },

        setTitle: function (title) {
            if (this._titleElement) {
                this._titleElement.empty();
                this.fill(this._titleElement, title);
            }
            return this;
        },

        setText: function (text) {
            if (this._textElement) {
                this._textElement.empty();
                this.fill(this._textElement, text);
            }
            return this;
        },

        fireForParent: function (event, element) {
            element = element.getParent();
            if (!element || element == document.body) return;
            if (element.retrieve('tip:enter')) element.fireEvent('mouseenter', event);
            else this.fireForParent(event, element);
        },

        elementMove: function (event, element) {
            this.position(event);
        },

        position: function (event, element) {
            if (!this.tip) document.id(this);

            var size = window.getSize(), scroll = window.getScroll(),
                tip = {x: this.tip.offsetWidth, y: this.tip.offsetHeight},
                props = {x: 'left', y: 'top'},
                bounds = {y: false, x2: false, y2: false, x: false},
                obj = {},
                offset;

            for (var z in props) {
                offset = this.options.offset[z].call ? this.options.offset[z].call(this, this.tip, element) : this.options.offset[z];
                obj[props[z]] = event.page[z] + offset;
                if (obj[props[z]] < 0) bounds[z] = true;
                if ((obj[props[z]] + tip[z] - scroll[z]) > size[z] - this.options.windowPadding[z]) {
                    obj[props[z]] = event.page[z] - offset - tip[z];
                    bounds[z + '2'] = true;
                }
            }

            this.fireEvent('bound', bounds);
            this.tip.setStyles(obj);
        },

        fill: function (element, contents) {
            if (typeof contents == 'string') element.set('html', contents);
            else element.adopt(contents);
        },

        show: function (element) {
            if (!this.tip) document.id(this);
            if (!this.tip.getParent()) this.tip.inject(document.body);
            this.fireEvent('show', [this.tip, element]);
        },

        hide: function (element) {
            if (!this.tip) document.id(this);
            this.fireEvent('hide', [this.tip, element]);
        }

    });

})();


// CSS3 animations
Element.Styles.MozTransform = "rotateY(@deg) scale(@)";
Element.Styles.MsTransform = "rotateY(@deg) scale(@)";
Element.Styles.OTransform = "rotateY(@deg) scale(@)";
Element.Styles.WebkitTransform = "rotateY(@deg) scale(@)";

Object.append(Fx.CSS.Parsers, {

    TransformScale: {
        parse: function (value) {
            return ((value = value.match(/^scale\((([0-9]*\.)?[0-9]+)\)$/i))) ? parseFloat(value[1]) : false;
        },
        compute: function (from, to, delta) {
            return Fx.compute(from, to, delta);
        },
        serve: function (value) {
            return 'scale(' + value + ')';
        }
    }

});

Object.append(Fx.CSS.Parsers, {

    TransformRotateY: {
        parse: function (value) {
            return ((value = value.match(/^rotateY\(([-]*([0-9]*\.)?[0-9]+)deg\)$/i))) ? parseFloat(value[1]) : false;
        },
        compute: function (from, to, delta) {
            return Fx.compute(from, to, delta);
        },
        serve: function (value) {
            return 'rotateY(' + value + 'deg)';
        }
    }

});

var getTransformProperty = function () {
    var test_properties = {
        computed: ['transformProperty', 'WebkitTransform', 'MozTransform', 'OTransform', 'msTransform'],
        raw: ['transform', '-webkit-transform', '-moz-transform', '-o-transform', 'msTransform']
    };
    var testEl = new Element("div");
    var property = null;
    var raw_property = null;
    property = test_properties.computed.some(function (el, index) {
        var test = el in testEl.style;
        if (test) {
            raw_property = test_properties.raw[index];
        }
        return test;
    });
    return raw_property;
};


/*
 ---
 description: DynamicTextarea

 license: MIT-style

 authors:
 - Amadeus Demarzi (http://amadeusamade.us)

 requires:
 core/1.3: [Core/Class, Core/Element, Core/Element.Event, Core/Element.Style, Core/Element.Dimensions]

 provides: [DynamicTextarea]
 ...
 */

(function () {

// Prevent the plugin from overwriting existing variables
    if (this.DynamicTextarea) return;

    var DynamicTextarea = this.DynamicTextarea = new Class({

        Implements: [Options, Events],

        options: {
            value: '',
            minRows: 1,
            delay: true,
            lineHeight: null,
            offset: 0,
            padding: 0

            // AVAILABLE EVENTS
            // onCustomLineHeight: (function) - custom ways of determining lineHeight if necessary

            // onInit: (function)

            // onFocus: (function)
            // onBlur: (function)

            // onKeyPress: (function)
            // onResize: (function)

            // onEnable: (function)
            // onDisable: (function)

            // onClean: (function)
        },

        textarea: null,

        initialize: function (textarea, options) {
            this.textarea = document.id(textarea);
            if (!this.textarea) return;

            this.setOptions(options);

            this.parentEl = new Element('div', {
                styles: {
                    padding: 0,
                    margin: 0,
                    border: 0,
                    height: 'auto',
                    width: 'auto'
                }
            })
                .inject(this.textarea, 'after')
                .adopt(this.textarea);

            // Prebind common methods
            ['focus', 'delayCheck', 'blur', 'scrollFix', 'checkSize', 'clean', 'disable', 'enable', 'getLineHeight']
                .each(function (method) {
                    this[method] = this[method].bind(this);
                }, this);

            // Firefox and Opera handle scroll heights differently than all other browsers
            if (window.Browser.firefox || window.Browser.opera) {
                this.options.offset =
                    parseInt(this.textarea.getStyle('padding-top'), 10) +
                        parseInt(this.textarea.getStyle('padding-bottom'), 10) +
                        parseInt(this.textarea.getStyle('border-bottom-width'), 10) +
                        parseInt(this.textarea.getStyle('border-top-width'), 10);
            } else {
                this.options.offset =
                    parseInt(this.textarea.getStyle('border-bottom-width'), 10) +
                        parseInt(this.textarea.getStyle('border-top-width'), 10);
                this.options.padding =
                    parseInt(this.textarea.getStyle('padding-top'), 10) +
                        parseInt(this.textarea.getStyle('padding-bottom'), 10);
            }

            // Disable browser resize handles, set appropriate styles
            this.textarea.set({
                'rows': 1,
                'styles': {
                    'resize': 'none',
                    '-moz-resize': 'none',
                    '-webkit-resize': 'none',
                    'position': 'relative',
                    'display': 'block',
                    'overflow': 'hidden',
                    'height': 'auto'
                }
            });

            this.getLineHeight();
            this.fireEvent('customLineHeight');

            // Set the height of the textarea, based on content
            this.checkSize(true);
            this.textarea.addEvent('focus', this.focus);
            this.fireEvent('init', [textarea, options]);
        },

        // This is the only crossbrowser method to determine ACTUAL lineHeight in a textarea (that I am aware of)
        getLineHeight: function () {
            var backupValue = this.textarea.value;
            this.textarea.value = 'M';
            this.options.lineHeight = this.textarea.getScrollSize().y - this.options.padding;
            this.textarea.value = backupValue;
            this.textarea.setStyle('height', this.options.lineHeight * this.options.minRows);
        },

        // Stops a small scroll jump on some browsers
        scrollFix: function () {
            this.textarea.scrollTo(0, 0);
        },

        // Add interactive events, and fire focus event
        focus: function () {
            this.textarea.addEvents({
                'keydown': this.delayCheck,
                'keypress': this.delayCheck,
                'click': this.delayCheck,
                'blur': this.blur,
                'scroll': this.scrollFix
            });
            return this.fireEvent('focus');
        },

        // Clean out extraneaous events, and fire blur event
        blur: function () {
            this.textarea.removeEvents({
                'keydown': this.delayCheck,
                'keypress': this.delayCheck,
                'blur': this.blur,
                'scroll': this.scrollFix
            });
            return this.fireEvent('blur');
        },

        // Delay checkSize because text hasn't been injected into the textarea yet
        delayCheck: function () {
            if (this.options.delay === true)
                this.options.delay = this.checkSize.delay(1);
        },

        // Determine if it needs to be resized or not, and resize if necessary
        checkSize: function (forced) {
            var oldValue = this.options.value,
                modifiedParent = false;

            this.options.value = this.textarea.value;
            this.options.delay = false;

            if (this.options.value === oldValue && forced !== true)
                return this.options.delay = true;

            if (!oldValue || this.options.value.length < oldValue.length || forced) {
                modifiedParent = true;
                this.parentEl.setStyle('height', this.parentEl.getSize().y);
                this.textarea.setStyle('height', this.options.minRows * this.options.lineHeight);
            }

            var tempHeight = this.textarea.getScrollSize().y,
                offsetHeight = this.textarea.offsetHeight,
                cssHeight = tempHeight - this.options.padding,
                scrollHeight = tempHeight + this.options.offset;

            if (scrollHeight !== offsetHeight && cssHeight > this.options.minRows * this.options.lineHeight) {
                this.textarea.setStyle('height', cssHeight);
                this.fireEvent('resize');
            }

            if (cssHeight > this.options.maxRows * this.options.lineHeight) {
                this.textarea.setStyle('height', this.options.maxRows * this.options.lineHeight);
                if (cssHeight - this.options.maxRows * this.options.lineHeight > 5) {
                    this.fireEvent('showError');
                } else {
                    this.fireEvent('hideError');
                }
                this.fireEvent('resize');
            } else {
                this.fireEvent('hideError');
            }

            if (modifiedParent) this.parentEl.setStyle('height', 'auto');

            this.options.delay = true;
            if (forced !== true)
                return this.fireEvent('keyPress');
        },

        // Clean out this textarea's event handlers
        clean: function () {
            this.textarea.removeEvents({
                'focus': this.focus,
                'keydown': this.delayCheck,
                'keypress': this.delayCheck,
                'blur': this.blur,
                'scroll': this.scrollFix
            });
            return this.fireEvent('clean');
        },

        // Disable the textarea
        disable: function () {
            this.textarea.blur();
            this.clean();
            this.textarea.set(this.options.disabled, true);
            return this.fireEvent('disable');
        },

        // Enables the textarea
        enable: function () {
            this.textarea.addEvents({
                'focus': this.focus,
                'scroll': this.scrollFix
            });
            this.textarea.set(this.options.disabled, false);
            return this.fireEvent('enable');
        }
    });

})();

/*
 ---

 script: String.Extras.js

 name: String.Extras

 description: Extends the String native object to include methods useful in managing various kinds of strings (query strings, urls, html, etc).

 license: MIT-style license

 authors:
 - Aaron Newton
 - Guillermo Rauch
 - Christopher Pitt

 requires:
 - Core/String
 - Core/Array
 - MooTools.More

 provides: [String.Extras]

 ...
 */

(function () {

    var special = {
            'a': /[àáâãäåăą]/g,
            'A': /[ÀÁÂÃÄÅĂĄ]/g,
            'c': /[ćčç]/g,
            'C': /[ĆČÇ]/g,
            'd': /[ďđ]/g,
            'D': /[ĎÐ]/g,
            'e': /[èéêëěę]/g,
            'E': /[ÈÉÊËĚĘ]/g,
            'g': /[ğ]/g,
            'G': /[Ğ]/g,
            'i': /[ìíîï]/g,
            'I': /[ÌÍÎÏ]/g,
            'l': /[ĺľł]/g,
            'L': /[ĹĽŁ]/g,
            'n': /[ñňń]/g,
            'N': /[ÑŇŃ]/g,
            'o': /[òóôõöøő]/g,
            'O': /[ÒÓÔÕÖØ]/g,
            'r': /[řŕ]/g,
            'R': /[ŘŔ]/g,
            's': /[ššş]/g,
            'S': /[ŠŞŚ]/g,
            't': /[ťţ]/g,
            'T': /[ŤŢ]/g,
            'u': /[ùúûůüµ]/g,
            'U': /[ÙÚÛŮÜ]/g,
            'y': /[ÿý]/g,
            'Y': /[ŸÝ]/g,
            'z': /[žźż]/g,
            'Z': /[ŽŹŻ]/g,
            'th': /[þ]/g,
            'TH': /[Þ]/g,
            'dh': /[ð]/g,
            'DH': /[Ð]/g,
            'ss': /[ß]/g,
            'oe': /[œ]/g,
            'OE': /[Œ]/g,
            'ae': /[æ]/g,
            'AE': /[Æ]/g
        },

        tidy = {
            ' ': /[\xa0\u2002\u2003\u2009]/g,
            '*': /[\xb7]/g,
            '\'': /[\u2018\u2019]/g,
            '"': /[\u201c\u201d]/g,
            '...': /[\u2026]/g,
            '-': /[\u2013]/g,
//	'--': /[\u2014]/g,
            '&raquo;': /[\uFFFD]/g
        },

        conversions = {
            ms: 1,
            s: 1000,
            m: 6e4,
            h: 36e5
        },

        findUnits = /(\d*.?\d+)([msh]+)/;

    var walk = function (string, replacements) {
        var result = string, key;
        for (key in replacements) result = result.replace(replacements[key], key);
        return result;
    };

    var getRegexForTag = function (tag, contents) {
        tag = tag || '';
        var regstr = contents ? "<" + tag + "(?!\\w)[^>]*>([\\s\\S]*?)<\/" + tag + "(?!\\w)>" : "<\/?" + tag + "([^>]+)?>",
            reg = new RegExp(regstr, "gi");
        return reg;
    };

    String.implement({

        standardize: function () {
            return walk(this, special);
        },

        repeat: function (times) {
            return new Array(times + 1).join(this);
        },

        pad: function (length, str, direction) {
            if (this.length >= length) return this;

            var pad = (str == null ? ' ' : '' + str)
                .repeat(length - this.length)
                .substr(0, length - this.length);

            if (!direction || direction == 'right') return this + pad;
            if (direction == 'left') return pad + this;

            return pad.substr(0, (pad.length / 2).floor()) + this + pad.substr(0, (pad.length / 2).ceil());
        },

        getTags: function (tag, contents) {
            return this.match(getRegexForTag(tag, contents)) || [];
        },

        stripTags: function (tag, contents) {
            return this.replace(getRegexForTag(tag, contents), '');
        },

        tidy: function () {
            return walk(this, tidy);
        },

        truncate: function (max, trail, atChar) {
            var string = this;
            if (trail == null && arguments.length == 1) trail = '…';
            if (string.length > max) {
                string = string.substring(0, max);
                if (atChar) {
                    var index = string.lastIndexOf(atChar);
                    if (index != -1) string = string.substr(0, index);
                }
                if (trail) string += trail;
            }
            return string;
        },

        ms: function () {
            // "Borrowed" from https://gist.github.com/1503944
            var units = findUnits.exec(this);
            if (units == null) return Number(this);
            return Number(units[1]) * conversions[units[2]];
        }

    });

})();


/*
 ---

 script: Element.Forms.js

 name: Element.Forms

 description: Extends the Element native object to include methods useful in managing inputs.

 license: MIT-style license

 authors:
 - Aaron Newton

 requires:
 - Core/Element
 - String.Extras
 - MooTools.More

 provides: [Element.Forms]

 ...
 */

Element.implement({

    tidy: function () {
        this.set('value', this.get('value').tidy());
    },

    getTextInRange: function (start, end) {
        return this.get('value').substring(start, end);
    },

    getSelectedText: function () {
        if (this.setSelectionRange) return this.getTextInRange(this.getSelectionStart(), this.getSelectionEnd());
        return document.selection.createRange().text;
    },

    getSelectedRange: function () {
        if (this.selectionStart != null) {
            return {
                start: this.selectionStart,
                end: this.selectionEnd
            };
        }

        var pos = {
            start: 0,
            end: 0
        };
        var range = this.getDocument().selection.createRange();
        if (!range || range.parentElement() != this) return pos;
        var duplicate = range.duplicate();

        if (this.type == 'text') {
            pos.start = 0 - duplicate.moveStart('character', -100000);
            pos.end = pos.start + range.text.length;
        } else {
            var value = this.get('value');
            var offset = value.length;
            duplicate.moveToElementText(this);
            duplicate.setEndPoint('StartToEnd', range);
            if (duplicate.text.length) offset -= value.match(/[\n\r]*$/)[0].length;
            pos.end = offset - duplicate.text.length;
            duplicate.setEndPoint('StartToStart', range);
            pos.start = offset - duplicate.text.length;
        }
        return pos;
    },

    getSelectionStart: function () {
        return this.getSelectedRange().start;
    },

    getSelectionEnd: function () {
        return this.getSelectedRange().end;
    },

    setCaretPosition: function (pos) {
        if (pos == 'end') pos = this.get('value').length;
        this.selectRange(pos, pos);
        return this;
    },

    getCaretPosition: function () {
        return this.getSelectedRange().start;
    },

    selectRange: function (start, end) {
        if (this.setSelectionRange) {
            this.focus();
            this.setSelectionRange(start, end);
        } else {
            var value = this.get('value');
            var diff = value.substr(start, end - start).replace(/\r/g, '').length;
            start = value.substr(0, start).replace(/\r/g, '').length;
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', start + diff);
            range.moveStart('character', start);
            range.select();
        }
        return this;
    },

    insertAtCursor: function (value, select) {
        var pos = this.getSelectedRange();
        var text = this.get('value');
        this.set('value', text.substring(0, pos.start) + value + text.substring(pos.end, text.length));
        if (select !== false) this.selectRange(pos.start, pos.start + value.length);
        else this.setCaretPosition(pos.start + value.length);
        return this;
    },

    insertAroundCursor: function (options, select) {
        options = Object.append({
            before: '',
            defaultMiddle: '',
            after: ''
        }, options);

        var value = this.getSelectedText() || options.defaultMiddle;
        var pos = this.getSelectedRange();
        var text = this.get('value');

        if (pos.start == pos.end) {
            this.set('value', text.substring(0, pos.start) + options.before + value + options.after + text.substring(pos.end, text.length));
            this.selectRange(pos.start + options.before.length, pos.end + options.before.length + value.length);
        } else {
            var current = text.substring(pos.start, pos.end);
            this.set('value', text.substring(0, pos.start) + options.before + current + options.after + text.substring(pos.end, text.length));
            var selStart = pos.start + options.before.length;
            if (select !== false) this.selectRange(selStart, selStart + current.length);
            else this.setCaretPosition(selStart + text.length);
        }
        return this;
    }

});


var initVotingButtons = function (buttons_holder) {
    if (!$(buttons_holder).hasClass('js-voting_buttons_will_show')) {
        $(buttons_holder).addClass('js-voting_buttons_will_show');
        $(buttons_holder).addEvent('mouseenter', function () {
            $$('.over').removeClass('over');
            $(buttons_holder).addClass('over');
        });
        $(buttons_holder).addEvent('mouseleave', function () {
            if (!$(buttons_holder).hasClass('js-animating')) {
                $(buttons_holder).removeClass('over');
            }
        });
        $(buttons_holder).addClass('over');
    }
};

var VoteHandler = new Class({
    initialize: function (voteResultsHandler) {
        this.voteResultsHandler = voteResultsHandler;
    },

    vote: function (id, className, type, event) {
        this.container = type == 'post' ? $('js-post_id_' + id) : $('js-comment_id_' + id);
        this.target = this.container.getElement('.' + className);

        if (this.container.hasClass('js-animating')) {
            return false;
        }
        this.id = id;
        this.type = type;
        this.value = !this.target.hasClass('vote_voted') ? (this.target.hasClass('vote_button_plus') ? '1' : '-1') : '0';

        // Анимируем кнопки: ставим - взрывается, убираем - схлопывается
        this.container.addClass('js-animating');
        if (this.value != '0') {
            this.container.addClass('js-animating');
            new futuAnimation({
                element: this.target,
                type: 'explode',
                element_class: 'vote_voted',
                holder_class: 'over',
                onComplete: (function () {
                    this.container.removeClass('js-animating');
                }).bind(this)
            });
        } else {
            (function () {
                this.target.addClass('hidden');
            }).bind(this).delay(30);
            new futuAnimation({
                element: this.target,
                type: 'hide',
                element_class: 'vote_voted',
                holder_class: 'over',
                onComplete: (function () {
                    this.container.removeClass('js-animating');
                    (function () {
                        this.target.removeClass('hidden');
                    }).bind(this).delay(300);
                }).bind(this)
            });
        }

        this.target.toggleClass('vote_voted');
        this.target
            .getSiblings('.vote_voted')
            .removeClass('vote_voted');

        this.sendRequest();
    },

    sendRequest: function () {
        var data = 'doc=' + this.id + '&vote=' + this.value;
        var url;

        if (this.type == 'comment') {
            url = ajaxUrls.comment_vote;
        } else {
            url = ajaxUrls.post_vote;
        }

        new futuAjax({
            button: this.target,
            color_to: '',
            color_from: '',
            url: url,
            data: data,
            onLoadFunction: function (response) {
                this.container.getElement('.vote_result').innerHTML = response.rating;
                this.voteResultsHandler.fireEvent('vote', [this.type, this.id]);
            }.bind(this),
            onCustomErrorFunction: function () {
                this.target.removeClass('vote_voted');
            }.bind(this)
        });
    }
});

var VoteResultsHandler = new Class({
    loadingData: false,
    activePage: 0,
    container: null,
    limit: 15,
    offset: 0,
    sendRequest: true,
    paginatorBuilt: false,
    target: null,
    type: null,
    rating: 0,

    Implements: Events,

    initialize: function () {
        // Подписываемся на событие голосования за карму
        this.addEvent('vote', function (type, id) {
            if (this.layer && !this.layer.hasClass('invisible') && type == this.type && this.id == id) {
                this.paginatorBuilt = false;
                this.offset = this.activePage * this.limit;
                this.loadData();
            }
        }.bind(this));

        window.addEvent('resize', function () {
            var body = $(document.body);
            if (body && !body.hasClass('l-touch_capable')) {
                this.hidePopup('fast');
            }
        }.bind(this));
    },

    // Отображение результатов голосования за комментарии и посты
    showVoteResult: function (id, type) {
        if (!this.loadingData) {
            var controlsContainer,
                currentTarget;

            if (type == 'karma') {
                currentTarget = $$('.b-karma_value')[0];
            } else {
                controlsContainer = type == 'post' ? $('js-post_id_' + id) : $('js-comment_id_' + id);
                currentTarget = controlsContainer.getElement('.vote_result');
            }

            this.initLayer();

            if (this.target) {
                if (this.target != currentTarget) {
                    this.hidePopup('fast');
                } else if (this.target == currentTarget && !this.layer.hasClass('invisible')) {
                    this.hidePopup();
                    return;
                }
            }

            // Попап скрыт и запрашиваются данные о результатах голосования для которых не выполняется запрос
            if (this.layer.hasClass('invisible') && !(this.target == currentTarget && !this.sendRequest)) {
                this.target = currentTarget;
                this.type = type;
                this.id = id;

                if (type == 'karma') {
                    this.layer.addClass('js-karma_popup');
                    this.container = this.target.getParent('.b-user_votes_container');
                } else {
                    this.controlsContainer = controlsContainer;
                    this.controlsContainerClone = this.controlsContainer.clone();
                    this.item = this.controlsContainer.getParent();
                    this.container = null;
                }

                this.show({
                    button: this.target,
                    id: id
                });
            }
        }
    },

    show: function (params) {
        this.params = params;
        this.loadData();

        this.boundHidePopupOnEscPress = this.hidePopupOnEscPress.bind(this);
        this.boundHidePopupOnBodyClick = this.hidePopupOnBodyClick.bind(this);
        window.addEvent('keydown', this.boundHidePopupOnEscPress);
        window.addEvent('click', this.boundHidePopupOnBodyClick);
    },

    loadData: function () {
        var data,
            url,
            color_to = 0,
            color_from = 0.5,
            attribute = 'opacity';

        if (this.type == 'karma') {
            data = 'user=' + this.params.id + '&limit=' + this.limit + '&offset=' + this.offset;
            url = ajaxUrls.user_karma_list;
        } else if (this.type == 'comment') {
            url = ajaxUrls.vote_list;
            data = 'comment=' + this.params.id + '&limit=' + this.limit + '&offset=' + this.offset;
        } else {
            url = ajaxUrls.vote_list;
            data = 'post=' + this.params.id + '&limit=' + this.limit + '&offset=' + this.offset;
        }

        this.sendRequest = false;

        (function (num, type, id) {
            var ajax = new futuAjax({
                button: this.params.button,
                animated_element: this.params.button,
                color_to: color_to,
                color_from: color_from,
                attribute: attribute,
                url: url,
                data: data,
                checkAjaxLoadedFunction: function () {
                    return this.loadingData;
                }.bind(this),
                setAjaxLoadingFunction: function () {
                    this.loadingData = true;
                }.bind(this),
                removeAjaxLoadingFunction: function () {
                    this.loadingData = false;
                }.bind(this),
                onLoadFunction: function (response) {
                    ajax.loading_animation_fx.cancel();
                    $(this.params.button).removeClass('js-lh_active');
                    this.params.button.setAttribute('style', '');

                    if (this.activePage == num && this.type == type && this.params.id == id) {
                        this.countVotes(response);

                        if (this.totalCount) {
                            if (!this.paginatorBuilt) {
                                this.buildPaginator();
                            }
                            this.setPage(0);
                        }
                        this.showPopup();
                        this.sendRequest = true;
                    }
                }.bind(this),
                onCustomErrorFunction: function () {
                    this.sendRequest = true;
                }.bind(this)

            });

        }.bind(this))(this.activePage, this.type, this.params.id);
    },

    countVotes: function (response) {
        this.plusArray = response.pros ? response.pros : [];
        this.minusArray = response.cons ? response.cons : [];
        this.totalCount = response.total_count;
        this.offset = response.offset;
        this.totalMinus = response.cons_count;
        this.totalPlus = response.pros_count;

        if (this.type == 'post' || this.type == 'comment') {
            this.rating = response.rating;
        } else if (this.type == 'karma') {
            this.karma = response.karma;
        }

        if (this.totalCount) {
            this.setHeaders();
        } else {
            this.buildPageNoVotes();
        }
    },

    setHeaders: function () {
        this.plusHeader.innerHTML = 'плюсов &mdash; ' + this.totalPlus;
        this.minusHeader.innerHTML = 'минусов &mdash; ' + this.totalMinus;
    },

    buildPaginator: function () {
        var pagesCount = Math.ceil(Math.max(this.totalMinus, this.totalPlus) / this.limit);

        if (pagesCount > 1) {
            var iHTMLpaginator = '',
                items;

            for (var i = 0; i < pagesCount; i++) {
                iHTMLpaginator += '<a href="#" class="b-pagination-item" data-page="' + i + '"> </a>';
            }

            this.paginator.innerHTML = iHTMLpaginator;
            this.paginator.removeClass('hidden');
            items = this.paginator.getElements('.b-pagination-item');
            this.paginatorBuilt = true;

            items.addEvent('click', function (event) {
                event.preventDefault();
                if (!this.loadingData) {
                    var target = event.target;

                    this.activePage = target.get('data-page');
                    this.offset = this.limit * this.activePage;
                    this.loadData();
                }
            }.bind(this))
        } else {
            this.paginator.innerHTML = '';
            this.paginator.addClass('hidden');
            this.prevPageLink.addClass('hidden');
            this.nextPageLink.addClass('hidden');
        }
    },

    buildPage: function () {
        var plusIHTML = this.buildCol(this.plusArray),
            minusIHTML = this.buildCol(this.minusArray);

        this.plusList.innerHTML = plusIHTML;
        this.minusList.innerHTML = minusIHTML;
        this.emptyResults.addClass('hidden');
        this.listsContainer.removeClass('hidden');
    },

    buildCol: function (arr) {
        var iHTML = '';
        if (arr) {
            arr.each(function (vote) {
                var value = (this.type == 'karma') ? '&nbsp;<span>(' + vote.vote + ')</span>' : '',
                    item;
                if (vote.user) {
                    if (vote.user.deleted) {
                        item = '<li><span class="b-removed_user">' + vote.user.login + '</span>' + value + '</li>';
                    } else {
                        item = '<li><a href="' + globals.parent_site + '/user/' + vote.user.login + '/" class="b_users_table-link">' + vote.user.login + '</a>' + value + '</li>';
                    }

                    iHTML += item;
                }
            }.bind(this));
        }
        return iHTML;
    },

    buildPageNoVotes: function () {
        this.plusList.innerHTML = '';
        this.minusList.innerHTML = '';
        this.plusHeader.innerHTML = '';
        this.minusHeader.innerHTML = '';
        this.paginator.innerHTML = '';
        this.paginator.addClass('hidden');
        this.prevPageLink.addClass('hidden');
        this.nextPageLink.addClass('hidden');
        this.listsContainer.addClass('hidden');
        this.emptyResults.removeClass('hidden');
        this.setPosition();
    },

    setPage: function (page, changePages) {
        var activeItem = this.paginator.getElement('.active');

        this.buildPage();
        this.setPosition();

        if (activeItem) {
            activeItem.removeClass('active');
        }

        if ($(this.paginator.getElements('a')[this.activePage])) {
            $(this.paginator.getElements('a')[this.activePage]).addClass('active');
        }

        if (!this.offset) {
            this.nextPageLink.addClass('hidden');
        } else {
            this.nextPageLink.removeClass('hidden');
        }

        if (this.activePage == 0) {
            this.prevPageLink.addClass('hidden');
        } else {
            this.prevPageLink.removeClass('hidden');
        }
    },

    setPosition: function () {

        if (this.type == 'karma') {
            if (this.layer.hasClass('invisible')) {
                var containerCoordinates = this.container.getCoordinates(),
                    containerCenterPos = containerCoordinates.left + containerCoordinates.width / 2,
                    layerCoords = this.layer.getCoordinates(),
                    layerWidth = parseInt(layerCoords.width, 10),
                    windowWidth = Math.max($$('.l-center_container')[0].getCoordinates().width, document.body.getCoordinates().width),
                    delta = windowWidth - (containerCenterPos + layerWidth / 2);

                this.layer.inject(this.container);
                this.layer.setStyle('margin-right', -layerWidth / 2 + (delta < 0 ? Math.abs(delta) : 0));
                $$('.l-center_container').setStyle('min-height', layerCoords.height + 50);
            }
        } else {
            var layerCoords = this.layer.getCoordinates(),
                controlsCoords = (this.item.getElement(this.controlsContainerClone)) ? this.controlsContainerClone.getCoordinates() : this.controlsContainer.getCoordinates(),
                leftConst = 5,
                topConst = 12,
                upPosition = controlsCoords.top + controlsCoords.height - layerCoords.height + topConst;

            // Если попап не умещается по высоте, его нужно спозиционировать относительно низа target-элемента
            if ((controlsCoords.top + layerCoords.height - topConst > window.getSize().y + window.getScroll().y && upPosition >= 0) || this.layer.hasClass('js-bottom')) {
                this.layer
                    .setStyles({
                        top: upPosition,
                        left: controlsCoords.left - leftConst,
                        margin: 0
                    })
                    .addClass('js-bottom');
            } else {
                if (this.layer.hasClass('invisible')) {
                    this.layer
                        .setStyles({
                            top: controlsCoords.top - topConst,
                            left: controlsCoords.left - leftConst,
                            margin: 0
                        });
                }
            }
        }

    },

    initLayer: function () {
        if (!$('js-votes_popup')) {
            this.layer = new Element('div', {
                'class': 'b-votes_popup invisible',
                'id': 'js-votes_popup'
            });
            this.layer.innerHTML = '<a href="#" class="b-close_btn"></a>\
				<div class="b_users_table_holder">\
				<a class="b-arrow b-arrow__prev" href="#"><i class="b-arrow-ico"></i></a>\
				<table class="b_users_table" cellspacing="0">\
				<tbody>\
					<tr>\
						<td class="b_users_table-cell b_users_table-cell__left">\
							<h4 class="b_users_table-subtitle"></h4>\
							<ul class="b_users_table-list"></ul>\
						</td>\
						<td class="b_users_table-cell b_users_table-cell__right">\
							<h4 class="b_users_table-subtitle"></h4>\
							<ul class="b_users_table-list"></ul>\
						</td>\
					</tr>\
				</tbody>\
				</table>\
				<a class="b-arrow b-arrow__next" href="#"><i class="b-arrow-ico"></i></a>\
				<div class="b-no_votes hidden">Никто не голосовал</div>\
			</div>\
			<div class="b-pagination">\
				<div class="b-pagination-inner_1">\
					<div class="b-pagination-inner_2">\
					</div>\
				</div>\
				<div style="clear:both;"></div>\
			</div>';

            this.layer.inject(document.body);
            this.paginator = this.layer.getElement('.b-pagination-inner_2');
            this.listsContainer = this.layer.getElement('.b_users_table');
            this.plusHeader = this.layer.getElement('.b_users_table-cell__left .b_users_table-subtitle');
            this.plusList = this.layer.getElement('.b_users_table-cell__left ul');
            this.minusHeader = this.layer.getElement('.b_users_table-cell__right .b_users_table-subtitle');
            this.minusList = this.layer.getElement('.b_users_table-cell__right ul');
            this.prevPageLink = this.layer.getElement('.b-arrow__prev');
            this.nextPageLink = this.layer.getElement('.b-arrow__next');
            this.closeBtn = this.layer.getElement('.b-close_btn');
            this.emptyResults = this.layer.getElement('.b-no_votes');

            this.closeBtn.addEvent('click', function (event) {
                event.preventDefault();
                this.hidePopup();
            }.bind(this));

            this.prevPageLink.addEvent('click', function (event) {
                event.preventDefault();
                if (this.offset != 0 && this.sendRequest) {
                    this.activePage--;
                    this.offset = this.limit * this.activePage;
                    this.loadData();
                }
            }.bind(this));

            this.nextPageLink.addEvent('click', function (event) {
                event.preventDefault();
                if (this.offset != null && this.sendRequest) {
                    this.activePage++;
                    this.loadData();
                }
            }.bind(this));
        }
    },

    showPopup: function () {
        if (this.type == 'comment' || this.type == 'post') {
            var parent = this.item.getParent('.' + this.type);

            this.controlsContainerClone.getElement('.vote_result').innerHTML = this.rating;
            this.controlsContainer.getElement('.vote_result').innerHTML = this.rating;
            this.controlsContainerClone.inject(this.item);
            this.controlsContainer.inject(this.layer);

            if (parent && parent.get('id')) {
                this.layer.set('data-el_id', parent.get('id'));
            }
        } else if (this.type == 'karma') {
            this.container.getElement('.b-karma_value').innerHTML = this.karma;
        }

        /*if (this.layer.hasClass('invisible')) {
         this.layer
         .setStyle('opacity', 0)
         .removeClass('invisible');

         new Fx.Tween(this.layer, {
         duration: 200
         })
         .start('opacity', 1);
         }*/
    },

    hidePopup: function (duration) {
        var duration = duration || 200;

        // Приведение клонированного узла к актуальному состоянию
        if (this.controlsContainerClone) {
            this.controlsContainerClone.innerHTML = this.controlsContainer.clone().innerHTML;
        }

        if (duration === 'fast') {
            this.setDefaultParams();
        } else {
            /*new Fx.Tween(this.layer, {
             duration: duration
             })
             .start('opacity', 0)
             .addEvent('complete', function() {
             this.setDefaultParams();
             }.bind(this));*/
        }

        if (this.boundHidePopupOnEscPress) {
            window.removeEvent('keydown', this.boundHidePopupOnEscPress);
            this.boundHidePopupOnEscPress = null;
        }
        if (this.boundHidePopupOnBodyClick) {
            window.removeEvent('click', this.boundHidePopupOnBodyClick);
            this.boundHidePopupOnBodyClick = null;
        }
    },

    hidePopupOnEscPress: function (event) {
        if (event.code == 27) {
            this.hidePopup('fast');
        }
    },
    hidePopupOnBodyClick: function (event) {
        var hide;
        if (!this.layer.hasClass('invisible')) {
            if (event.target == this.layer || event.target.getParent('#js-votes_popup')) {
                hide = false;
            } else if (event.target.hasClass('b-user_votes_wrapper') || event.target.getParent('.b-user_votes_wrapper')) {
                hide = false;
            } else {
                hide = true;
            }
        }

        if (hide) {
            this.hidePopup('fast');
        }
    },

    setDefaultParams: function () {
        if (this.layer && !this.layer.hasClass('invisible')) {
            this.layer
                .inject(document.body)
                .addClass('invisible')
                .removeClass('js-bottom')
                .removeClass('js-karma_popup')
                .set('style', '');

            this.activePage = 0;
            this.offset = 0;
            this.paginatorBuilt = false;

            if (this.type == 'comment' || this.type == 'post') {
                this.controlsContainer.inject(this.item);
                this.controlsContainerClone.destroy();
            } else if (this.type == 'karma') {
                $$('.l-center_container').setStyle('min-height', '');
            }

        }
    }
});

var voteResultsHandler = new VoteResultsHandler();
var voteHandler = new VoteHandler(voteResultsHandler);

feedSettingsHandler = {
    switchFeedSorting: function (button, sorting) {
        var data = 'sorting=' + sorting;
        new futuAjax({
            button: $(button),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.feeds_sorting,
            data: data,
            dont_stop_animation: true,
            onLoadFunction: function (response) {
                window.location.href = '/';
            }
        });
    },
    switchFeedThreshold: function (button, sorting, threshold) {
        var data = 'sorting=' + sorting + '&threshold_value=' + threshold;
        new futuAjax({
            button: $(button).getNext('.threshold_select_button'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.feeds_threshold,
            data: data,
            dont_stop_animation: true,
            onLoadFunction: function (response) {
                window.location.href = '/';
            }
        });
    },
    switchFeedType: function (button, type) {
        var data = 'feed_type=' + type;
        new futuAjax({
            button: $(button),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.feeds_type,
            data: data,
            dont_stop_animation: true,
            onLoadFunction: function (response) {
                window.location.href = globals.base_domain ? globals.base_domain.url : '/';
            }
        });
    },
    subscribe: function (button, type, value, container, event) {

        feedSettingsHandler.setSubscription(button, type, value, 'subscribe', container);
    },
    unsubscribe: function (button, type, value, container, event) {

        feedSettingsHandler.setSubscription(button, type, value, 'unsubscribe', container);
    },
    setSubscription: function (button, type, value, switch_position, container) {
        var url = ajaxUrls.getSubscriptionUrl(type, switch_position);
        var data = '';
        if (type == 'users') {
            data += 'user=' + value;
        }
        if (type == 'domains') {
            data += 'domain=' + value;
        }
        new futuAjax({
            button: $(button),
            color_to: '1',
            color_from: '1',
            attribute: 'opacity',
            url: url,
            data: data,
            onLoadFunction: function (response) {
                var subscription_holder = $(button).getParent('.js-subscribe_controls');
                var subsribers_delta = 0;
                var containerEl = $(container) || $(button).getParent('.' + container);
                var count;
                var countHolder;
                var descriptionHolder;
                if (switch_position == 'subscribe') {
                    subsribers_delta++;
                    $(button).addClass('b-fui_icon_button_subscribed');
                    $(button).getElement('i').innerHTML = 'вы подписаны';
                    $(button).addEvent('mouseout', function () {
                        subscription_holder.getElement('.js-unsubscribe_text').innerHTML = 'отписаться';
                        (function () {
                            $(button).removeEvents('mouseout');
                            $(button).removeClass('b-fui_icon_button_subscribed');
                            subscription_holder.addClass('js-subscribed');
                        }).delay(1000);
                    });
                } else {

                    subsribers_delta--;
                    $(button).addClass('b-fui_icon_button_unsubscribed');
                    $(button).getElement('.js-unsubscribe_text').innerHTML = 'вы отписаны';
                    $(button).addEvent('mouseout', function () {
                        subscription_holder.getElement('.b-fui_icon_button_subscribe i').innerHTML = 'подписаться';
                        (function () {
                            $(button).removeEvents('mouseout');
                            $(button).removeClass('b-fui_icon_button_unsubscribed');
                            subscription_holder.removeClass('js-subscribed');
                        }).delay(1000);
                    });
                }
                if (containerEl) {
                    countHolder = containerEl.getElement('.b-subscribers_count');
                    descriptionHolder = containerEl.getElement('.b-subscribers_description');
                }
                if (countHolder && descriptionHolder) {
                    count = parseInt(countHolder.innerHTML, 10) + subsribers_delta;
                    countHolder.innerHTML = parseInt(countHolder.innerHTML, 10) + subsribers_delta;
                    descriptionHolder.innerHTML = utils.getPlural(count, ['подписчик', 'подписчика', 'подписчиков']);
                }
            }
        });
    },
    ignore: function (button, type, value, container) {
        feedSettingsHandler.setIgnoring(button, type, value, 'ignore', container);
    },
    unignore: function (button, type, value, container) {
        feedSettingsHandler.setIgnoring(button, type, value, 'unignore', container);
    },
    setIgnoring: function (button, type, value, switch_position, container) {
        var url = ajaxUrls.getSubscriptionUrl(type, switch_position);
        var data = '';
        if (type == 'users') {
            data += 'users=' + value;
        }
        if (type == 'domains') {
            data += 'domain=' + value;
        }
        new futuAjax({
            button: $(button),
            color_to: '0.5',
            color_from: '1',
            attribute: 'opacity',
            url: url,
            data: data,
            onLoadFunction: function (response) {
                var subscription_holder = $(button).getParent('.js-subscribe_controls');
                if (switch_position == 'ignore') {
                    subscription_holder.addClass('js-ignored');
                } else {
                    subscription_holder.removeClass('js-ignored');
                }
            }
        });
    },
    shrimIndexForSubscriptionMessage: function () {
        $$('.l-content_aside').set('styles', {
            'zIndex': 1
        });
        $$('.b-header_counters, .l-i-content_main').set('styles', {
            'position': 'static'
        });
        $$('.b-expand-button').set('styles', {
            'zIndex': 1000
        });
        var header_element = $$('.l-header-wrapper')[0];
        (new Element('div', {
            'class': 'clear'
        })).inject(header_element);
        (new Element('div', {
            'class': 'b-no_posts_in_subscriptions_shrim'
        })).inject(header_element);

        (new Element('div', {
            'class': 'b-no_posts_in_subscriptions_shrim'
        })).inject($('js-footer'));

        var subscriptions_link_element = $$('.b-header_counters_subscriptions')[0];
        subscriptions_link_element.addClass('b-header_counters_subscriptions_unshrimmed');

        $$('.l-footer').set('styles', {
            'marginTop': '-177px'
        });

        $$('.b-no_posts_in_subscriptions_shrim').addClass('b-no_posts_in_subscriptions_shrim_active');

    }
};

userMenuHandler = {
    show: function () {
        var button = $$('.b-menu_item__user_menu')[0],
            headerContainer = $$('.l-header')[0],
            buttonCoordinates = button.getCoordinates(),
            container = $('js-header_nav_user_menu'),
            initialWidth = 59,
            initialHeight = 26,
            coordinates;

        if ($(document.body).hasClass('l-600')) {
            $('js-header_nav_user_menu').setStyles({
                right: $(document).getCoordinates().width - buttonCoordinates.left - 27 + 'px',
                left: 'auto'
            });
        } else {
            $('js-header_nav_user_menu').setStyles({
                right: 'auto',
                left: buttonCoordinates.left + 1 + 'px'
            });
        }
        container.setStyles({
            top: -10000,
            width: 'auto',
            height: 'auto'
        });
        container.removeClass('hidden');
        coordinates = container.getCoordinates();
        container.setStyles({
            top: buttonCoordinates.top - headerContainer.getCoordinates().top - 5,
            width: initialWidth,
            height: initialHeight
        });

        container.set('morph', {duration: 222, link: 'cancel', onComplete: function () {
            document.addEvent('click', userMenuHandler.hide);
        }});

        container.morph({'width': coordinates.width, height: coordinates.height});
    },
    hide: function () {
        document.removeEvent('click', userMenuHandler.hide);
        $('js-header_nav_user_menu').get('morph').cancel();
        $('js-header_nav_user_menu').addClass('hidden');
        $('js-header_nav_user_menu').setAttribute('style', '');
    }
};
domainsSelector = {
    query_input_focused: false,
    selectBaseDomain: function (domain_id, expected_value, actual_value) {
        var submit_domain_el = $('js-submit_domain');
        if (expected_value != '' && actual_value != '') {
            this.showKarmaLimitError('заглавную', expected_value, actual_value);
            if (submit_domain_el) {
                submit_domain_el.innerHTML = '';
            }
        } else {
            var found_domains_element = $('js-new_post_domain_found_domains');
            if (found_domains_element) {
                found_domains_element.addClass('hidden');
            }
            var new_domain_element = $('js-new_post_domain');
            var selected_index_element = new_domain_element.getElement('.b-new_post_domain_selected_index');
            var selected_subdomain_element = new_domain_element.getElement('.b-new_post_domain_selected_subdomain');

            selected_index_element.removeClass('hidden');
            selected_subdomain_element.addClass('hidden');

            new_domain_element.getElement('.i-form_text_input').value = '';
            $('js-new_post_form').getElement('.js-new_post_domain_selected').value = domain_id;
            if (submit_domain_el) {
                submit_domain_el.innerHTML = ' на <a href="/" target="_blank">заглавную</a>';
            }
        }
    },
    removeSelectedBaseDomain: function (focus_input) {
        var new_domain_element = $('js-new_post_domain');
        var selected_index_element = new_domain_element.getElement('.b-new_post_domain_selected_index');
        var selected_subdomain_element = new_domain_element.getElement('.b-new_post_domain_selected_subdomain');
        var submit_domain_el = $('js-submit_domain');

        if (selected_subdomain_element.hasClass('hidden')) {
            new_domain_element.set('styles', {
                'maxWidth': new_domain_element.getSize().x + 'px'
            });
            new_domain_element.set('morph', {duration: 333, link: 'cancel'});

            selected_subdomain_element.set('styles', {
                'opacity': 0
            });
            selected_subdomain_element.set('morph', {duration: 222, link: 'cancel'});

            selected_index_element.addClass('hidden');
            selected_subdomain_element.removeClass('hidden');

            new_domain_element.morph({'maxWidth': 1500});
            selected_subdomain_element.morph({'opacity': 1});

            new_domain_element.getElement('.i-form_text_input').value = '';
            if (focus_input) {
                new_domain_element.getElement('.i-form_text_input').focus();
            }
            if (submit_domain_el) {
                submit_domain_el.innerHTML = '';
            }
        }
    },
    selectDomain: function (domain_id, domain_name, errors) {
        var submit_domain_el = $('js-submit_domain');
        if (errors) {
            var message = '';
            for (var i = 0, l = errors.length; i < l; i++) {
                message += localMessages.getErrorMessageByErrorCode(errors[i].code, '/create/post/', errors[i]);
            }
            if (message != '') {
                new futuAlert(message, false);
            }
            $('js-new_post_domain').getElement('.i-form_text_input').value = '';
            if (submit_domain_el) {
                submit_domain_el.innerHTML = '';
            }
        } else {
            var domain_name_part = domain_name.split('.')[0];
            domainsSelector.removeSelectedBaseDomain();
            var new_domain_element = $('js-new_post_domain');
            new_domain_element.getElement('.i-form_text_input').value = domain_name_part;
            if (submit_domain_el) {
                submit_domain_el.innerHTML = ' на <a href="http://' + domain_name + '" target="_blank">' + domain_name_part + '</a>';
            }
            $('js-new_post_form').getElement('.js-new_post_domain_selected').value = domain_id;
            if ($('js-domain_in_selector_' + domain_id)) {
                ajaxHandler.highlightField($('js-domain_in_selector_' + domain_id), 1, 0.5, 'opacity');
            }
        }
        $('js-new_post_domain_found_domains').addClass('hidden');
    },
    selectMatchingDomain: function () {
        var new_domain_element = $('js-new_post_domain');
        var new_domain_query_value = new_domain_element.getElement('.i-form_text_input').value.trim();
        var found_domains_element = $('js-new_post_domain_found_domains');
        var found_domain_elements = found_domains_element.getElements('a');
        for (var i = 0; i < found_domain_elements.length; i++) {
            var found_domain_element = found_domain_elements[i];
            var found_domain_element_url_part = found_domain_element.getAttribute('data-domain_url_part');
            if (new_domain_query_value == found_domain_element_url_part) {
                found_domain_element.onclick();
                break;
            }
        }
    },
    unSelectDomain: function () {
        $('js-new_post_domain_found_domains').addClass('hidden');
        domainsSelector.removeSelectedBaseDomain();
        var new_domain_element = $('js-new_post_domain');
        $('js-new_post_form').getElement('.js-new_post_domain_selected').value = '';
    },
    search_domain_timeout: null,
    onQueryInputKeyPress: function (event, domain_id, expected_value, actual_value) {
        var query_input = $('js-new_post_domain').getElement('.i-form_text_input');
        var found_domains_element = $('js-new_post_domain_found_domains');

        // prevent default for up and down
        if (event.keyCode == 38 || event.keyCode == 40) {
            var e = new Event(event);
            e.preventDefault();
        }
        if (event.keyCode < 37 || event.keyCode > 40) { //everything but arrow keys
            if (event.keyCode == 13) {
                event.preventDefault();
                // enter - select active domain if results opened
                if (!found_domains_element.hasClass('hidden')) {
                    eval(found_domains_element.getElement('.b-new_post_domain_found_domains_selected a').getAttribute('onclick'));
                    // enter - search domains
                } else {
                    window.clearTimeout(domainsSelector.search_domain_timeout);
                    domainsSelector.search_domain_timeout = null;
                    domainsSelector.search_domain_timeout = window.setTimeout(domainsSelector.searchDomains, 300);
                    if (query_input.value.toLocaleLowerCase() == 'заглавную') {
                        domainsSelector.selectBaseDomain(domain_id, expected_value, actual_value);
                    }
                }
                // escape - hide results
            } else if (event.keyCode == 27) {
                found_domains_element.addClass('hidden');
                domainsSelector.onQueryInputBlur();
                // tab - auto match domain if results are loaded
            } else if (event.keyCode == 9) {
                if (!found_domains_element.hasClass('hidden')) {
                    domainsSelector.selectMatchingDomain();
                } else {
                    window.clearTimeout(domainsSelector.search_domain_timeout);
                    domainsSelector.search_domain_timeout = null;
                    domainsSelector.search_domain_timeout = window.setTimeout(domainsSelector.searchDomains, 300);
                }
                // everything else - search
            } else {
                domainsSelector.unSelectDomain();
                window.clearTimeout(domainsSelector.search_domain_timeout);
                domainsSelector.search_domain_timeout = null;
                domainsSelector.search_domain_timeout = window.setTimeout(domainsSelector.searchDomains, 300);
            }
        } else {
            if (!found_domains_element.hasClass('hidden')) {
                var active_domain = found_domains_element.getElement('.b-new_post_domain_found_domains_selected');
                var next_active_domain = active_domain;
                // up - select previous
                if (event.keyCode == 38) {
                    if (active_domain.getPrevious('li')) {
                        next_active_domain = active_domain.getPrevious('li');
                    } else {
                        next_active_domain = found_domains_element.getLast('li');
                    }
                    // down - select next
                } else if (event.keyCode == 40) {
                    if (active_domain.getNext('li')) {
                        next_active_domain = active_domain.getNext('li');
                    } else {
                        next_active_domain = found_domains_element.getElement('li');
                    }
                }
                active_domain.removeClass('b-new_post_domain_found_domains_selected');
                next_active_domain.addClass('b-new_post_domain_found_domains_selected');
                domainsSelector.scrollToFocusedDomain(next_active_domain);
            } else {
                // down - search if results are hidden
                if (event.keyCode == 40) {
                    window.clearTimeout(domainsSelector.search_domain_timeout);
                    domainsSelector.search_domain_timeout = null;
                    domainsSelector.searchDomains();
                }
            }
        }
    },
    scrollToFocusedDomain: function (domain_element) {
        var domains_holder = $('js-new_post_domain_found_domains');
        var domains_holder_size = domains_holder.getSize();
        domains_holder_size.y = domains_holder_size.y - 2;
        var domains_holder_scroll = domains_holder.getScroll();

        var domain_element_position = domain_element.getPosition(domains_holder);
        var domain_element_size = domain_element.getSize();

        if (domain_element_position.y < 0) {
            domains_holder.scrollTo(0, domains_holder_scroll.y + domain_element_position.y);
        }
        if (domain_element_position.y + domain_element_size.y > domains_holder_size.y) {

            domains_holder.scrollTo(0, domains_holder_scroll.y + domain_element_position.y - domains_holder_size.y + domain_element_size.y);
        }
    },
    onQueryInputFocus: function () {
        domainsSelector.query_input_focused = true;
    },
    onQueryInputBlur: function () {
        domainsSelector.query_input_focused = false;
        var found_domains_element = $('js-new_post_domain_found_domains');
        if (!found_domains_element.hasClass('hidden')) {
            domainsSelector.selectMatchingDomain();
        }
    },
    searchDomains: function () {
        window.clearTimeout(domainsSelector.search_domain_timeout);
        domainsSelector.search_domain_timeout = null;

        var query_input = $('js-new_post_domain').getElement('.i-form_text_input');
        var data = 'query=' + query_input.value;

        if (query_input.value.length > 1) {
            new futuAjax({
                button: query_input,
                attribute: 'opacity',
                color_to: 1,
                color_from: 1,
                url: ajaxUrls.search_domains_hostname,
                data: data,
                onLoadFunction: function (response) {
                    domainsSelector.showFoundDomains(response.domains);
                    if (!domainsSelector.query_input_focused) {
                        domainsSelector.selectMatchingDomain();
                    }
                }
            });
        }
    },
    showFoundDomains: function (domains) {
        var found_domains_element = $('js-new_post_domain_found_domains');
        if (domains && domains.length > 0) {
            found_domains_element.removeClass('hidden');
            var iHTML = '';
            for (var i = 0; i < domains.length; i++) {
                var domain = domains[i];
                var domain_url_part = domain.url.split('.')[0];
                var domain_name = null;
                var errors = false;
                var error;

                if (domain.title && domain.title.length > 0) {
                    domain_name = domain.title;
                } else if (domain.name && domain.name.length > 0) {
                    domain_name = domain.name;
                }

                if (domain.create_post_validation_error) {
                    errors = [];
                    for (var k in domain.create_post_validation_error) {
                        if (domain.create_post_validation_error.hasOwnProperty(k)) {
                            error = domain.create_post_validation_error[k];
                            error['domain'] = domain.url;
                            errors.push(error)
                        }
                    }
                    errors = JSON.encode(errors);
                }
                iHTML += '<li><a href="#" data-domain_url_part="{domain_url_part}" onclick=\'domainsSelector.selectDomain("{domain_id}", "{domain_url}", {errors}); return false;\'><span>{domain_url}</span>{domain_name}</a></li>'.substitute({
                    domain_id: domain.id,
                    domain_url: domain.url,
                    domain_url_part: domain.url.split('.')[0],
                    domain_name: domain_name ? '<br>' + domain_name : '',
                    errors: errors
                });
            }
            found_domains_element.innerHTML = iHTML;
            found_domains_element.getElement('li').addClass('b-new_post_domain_found_domains_selected');
        } else {
            found_domains_element.addClass('hidden');
            found_domains_element.innerHTML = '';
        }
    },
    showKarmaLimitError: function (domain, expected_value, actual_value) {
        new futuAlert('Для того чтобы написать пост на&nbsp;{domain}, нужна карма выше {expected}, а у вас она {actual}.'.substitute({
            domain: domain,
            expected: expected_value,
            actual: actual_value
        }));
    }
};
topPanelHandler = {

    setSubsitePage: function (button_element) {
        var page_id = button_element.getAttribute('data-page');
        var page_element = $('js-top_panel_subsites_list').getElement('.b-subsites-list-page[data-page="' + page_id + '"]');
        $('js-top_panel_subsites_list').getElement('.active').removeClass('active');
        $('js-top_panel_subsites_list_pagination').getElement('.active').removeClass('active');

        button_element.addClass('active');
        page_element.addClass('active');

        if (!button_element.getPrevious('.b-pagination-item')) {
            $('js-top_panel_subsites_list_pagination_prev').addClass('hidden');
        } else {
            $('js-top_panel_subsites_list_pagination_prev').removeClass('hidden');
        }
        if (!button_element.getNext('.b-pagination-item')) {
            $('js-top_panel_subsites_list_pagination_next').addClass('hidden');
        } else {
            $('js-top_panel_subsites_list_pagination_next').removeClass('hidden');
        }
    },
    setPreviousSubsitePage: function () {
        var button_element = $('js-top_panel_subsites_list_pagination').getElement('.active');
        var previous_button_element = button_element.getPrevious('.b-pagination-item');
        topPanelHandler.setSubsitePage(previous_button_element);
    },
    setNextSubsitePage: function () {
        var button_element = $('js-top_panel_subsites_list_pagination').getElement('.active');
        var next_button_element = button_element.getNext('.b-pagination-item');
        topPanelHandler.setSubsitePage(next_button_element);
    },

    // Раскрытие/скрытие верхней панели
    togglePanel: function () {
        $('js-top_panel').style.top = $$('.b-top_horizontal_banner').length > 0 ? '230px' : '70px';
        if ($('js-top_panel').hasClass('js-opened')) {
            $('js-top_panel').removeClass('js-opened');
            document.body.removeEvent('click', topPanelHandler.togglePanel);
        } else {
            $('js-top_panel').addClass('js-opened');
            (function () {
                document.body.addEvent('click', topPanelHandler.togglePanel)
            }).delay(100);
        }
    }

};
subscriptionsHandler = {
    renderPage: function () {
        subscriptionsHandler.renderPageNavigation();
        subscriptionsHandler.renderSectionNavigation();
        subscriptionsHandler.renderModeNavigation();
        subscriptionsHandler.renderSortNavigation();
        subscriptionsHandler.renderTagNavigation();
        subscriptionsHandler.renderContent();
        subscriptionsHandler.loadData();
    },
    renderPageNavigation: function () {
        //$$('.b-header_counters_subscriptions').addClass('b-header_counters_subscriptions_active');
    },
    renderSectionNavigation: function () {
        var buttons = $$('.b-aside_navigation_item_title[data-section="' + globals.uri_directory[0] + '"]');
        if (buttons.length > 0) {
            asideNavigationHandler.selectItem(buttons[0]);
        }
    },
    renderModeNavigation: function () {
        var buttons = $$('.b-blog_nav_sort_link[href="/' + globals.uri_directory.slice(0, 2).join('/') + '/"], .b-blog_nav_sort_link[href="' + document.location.href + '"]');
        if (buttons.length > 0) {
            modeNavigationHandler.selectItem(buttons[0]);
        } else {
            var first_button_element = $$('.l-subscription_content[data-navigation_id="' + globals.uri_directory[0] + '"] .b-blog_nav_sort_link')[0];
            modeNavigationHandler.selectItem(first_button_element);
        }
    },
    renderSortNavigation: function () {
        if (globals.uri_directory[1] && (globals.uri_directory[1] == 'subscribed' || globals.uri_directory[1] == 'ignored')) {
            sortNavigationHandler.hideNavigation();
            asideNavigationHandler.updateItemsUri(globals.uri_directory[1]);
        } else {
            sortNavigationHandler.showNavigation();
            var buttons = $$('.b-menu_link[href="' + globals.uri_directory_to_string + '"]');
            if (buttons.length > 0) {
                sortNavigationHandler.selectItem(buttons[0]);
            } else {
                var navigation_element = $$('.l-subscription_content[data-navigation_id="' + globals.uri_directory[0] + '"] .b-menu')[0];
                sortNavigationHandler.deselectAllItems(navigation_element);
            }
            asideNavigationHandler.updateItemsUri();
        }
    },
    renderTagNavigation: function () {
        if (globals.uri_directory[1] && (globals.uri_directory[1] == 'city' || globals.uri_directory[1] == 'tag')) {
            var buttons = $$('.b-cloud a[href="' + globals.uri_directory_to_string + '"]');
            if (buttons.length > 0) {
                tagNavigationHandler.selectItem(buttons[0]);
            }
        } else {
            tagNavigationHandler.deselectAllItems($('js-subscription_users_cities'));
            tagNavigationHandler.deselectAllItems($('js-subscription_domains_tags'));
        }
    },
    renderContent: function () {
        $A($$('*[data-navigation_id]')).each(function (page_section_element) {
            var section_navigation_id = page_section_element.getAttribute('data-navigation_id');
            var section_only_on_index = page_section_element.getAttribute('data-navigation_index_only');

            var new_navigation_id = globals.uri_directory.join('_');

            if (section_only_on_index) {
                if (section_navigation_id == new_navigation_id) {
                    page_section_element.removeClass('hidden');
                } else {
                    page_section_element.addClass('hidden');
                }
            } else {
                for (var i = 0; i < globals.uri_directory.length; i++) {
                    var new_navigation_id = '';
                    for (var j = 0; j <= i; j++) {
                        if (j > 0) {
                            new_navigation_id += '_';
                        }
                        new_navigation_id += globals.uri_directory[j];

                        if (section_navigation_id == new_navigation_id) {
                            page_section_element.removeClass('hidden');
                            break;
                        } else {
                            page_section_element.addClass('hidden');
                        }
                    }
                }
            }
        });
    },
    loadData: function () {
        if (globals.uri_directory[0] == 'blogs') {
            subscriptionsHandler.loadDomains(false);
            if (!$('js-subscription_domains_tags').getElement('a')) {
                moreHandler.loadDomainsTags($('js-subscription_users_cities'), {load_more: false});
            }
        }
        if (globals.uri_directory[0] == 'users') {
            subscriptionsHandler.loadUsers(false);
            if (!$('js-subscription_users_cities').getElement('a')) {
                moreHandler.loadUsersCities($('js-subscription_users_cities'), {load_more: false});
            }
        }
    },
    loadDomains: function (load_more_element) {
        var load_more = false;
        var navigation_id = globals.uri_directory.slice(0, 2).join('_');

        if (load_more_element) {
            load_more = true;
        } else {
            load_more_element = $$('*[data-navigation_id="' + navigation_id + '"] .b-blogs_list')[0];
        }
        if (load_more_element) {
            if (globals.uri_directory[1] == 'subscribed') {
                moreHandler.loadDomains(load_more_element, {load_more: load_more, sort: 'subscribed'});
            } else if (globals.uri_directory[1] == 'ignored') {
                moreHandler.loadDomains(load_more_element, {load_more: load_more, sort: 'ignored'});
            } else if (globals.uri_directory[1] == 'new') {
                moreHandler.loadDomains(load_more_element, {load_more: load_more, sort: 'new'});
            } else if (globals.uri_directory[1] == 'random') {
                moreHandler.loadDomains(load_more_element, {load_more: load_more, sort: 'random'});
            } else if (globals.uri_directory[1] == 'tag') {
                moreHandler.loadDomains(load_more_element, {load_more: load_more, sort: 'tag', tag: globals.uri_directory[2]});
            } else if (globals.uri_directory[1] == 'search') {
                moreHandler.loadDomains(load_more_element, {load_more: load_more, search: globals.uri_query.query, tag: globals.uri_directory[2]});
            } else if (globals.uri_directory[1] == 'top') {
                moreHandler.loadDomains(load_more_element, {load_more: load_more, sort: 'top'});
            } else {
                moreHandler.loadDomains(load_more_element, {load_more: load_more, sort: 'active'});
            }
        } else {
            new futuAlert('Список не найден.');
        }
    },
    loadUsers: function (load_more_element) {
        var load_more = false;
        var navigation_id = globals.uri_directory.slice(0, 2).join('_');

        if (load_more_element) {
            load_more = true;
        } else {
            load_more_element = $$('*[data-navigation_id="' + navigation_id + '"] .b-users_list')[0];
        }
        if (load_more_element) {
            if (globals.uri_directory[1] == 'subscribed') {
                moreHandler.loadUsers(load_more_element, {load_more: load_more, sort: 'subscribed'});
            } else if (globals.uri_directory[1] == 'ignored') {
                moreHandler.loadUsers(load_more_element, {load_more: load_more, sort: 'ignored'});
            } else if (globals.uri_directory[1] == 'karma') {
                moreHandler.loadUsers(load_more_element, {load_more: load_more, sort: 'by_karma'});
            } else if (globals.uri_directory[1] == 'random') {
                moreHandler.loadUsers(load_more_element, {load_more: load_more, sort: 'by_random'});
            } else if (globals.uri_directory[1] == 'city') {
                moreHandler.loadUsers(load_more_element, {load_more: load_more, sort: 'by_city', city: globals.uri_directory[2]});
            } else if (globals.uri_directory[1] == 'search') {
                moreHandler.loadUsers(load_more_element, {load_more: load_more, search: globals.uri_query.query, city: globals.uri_directory[2]});
            } else {
                moreHandler.loadUsers(load_more_element, {load_more: load_more, sort: 'by_popularity'});
            }
        } else {
            new futuAlert('Список не найден.');
        }
    },
    ignoreUserByLogin: function (form) {
        var input_element = $(form).getElement('input[name="users"]');
        if (input_element.value.trim().length > 0) {
            var data = $(form).toQueryString();
            var list_element = form.getParent('.l-subscription_list').getElement('.b-users_list');
            new futuAjax({
                button: $(input_element),
                color_to: Colors.background_color,
                color_from: Colors.inputs_bg_color,
                url: ajaxUrls.users_ignore,
                data: data,
                onLoadFunction: function (response) {
                    var list_page = new Element('div', {
                        html: response.template
                    });
                    $A(list_page.getElements('.b-list_item')).each(function (new_list_item) {
                        var new_list_item_id = new_list_item.getAttribute('data-user-id');
                        if (list_element.getElements('.b-list_item[data-user-id="' + new_list_item_id + '"]').length > 0) {
                            new_list_item.destroy();
                        }
                    });
                    list_page.inject(list_element, 'top');

                    form.reset();

                    subscriptionsHandler.closeListDescription('users_ignored');
                }
            });
        } else {
            ajaxHandler.highlightField(input_element);
        }
    },
    closeListDescription: function (list_id) {
        var list_description_element = $('js-subscription_' + list_id + '_description');
        list_description_element.set('morph', {
            duration: 333,
            onComplete: function () {
                list_description_element.addClass('hidden');
            }
        });
        list_description_element.set('styles', {
            'overflow': 'hidden'
        });

        list_description_element.morph({
            'height': 0,
            'paddingTop': 0,
            'paddingBottom': 0
        });

        var list_description_cookie = Cookie.write('list_description_' + list_id, '1', {
            duration: 365
        });
    }
};

modeNavigationHandler = {
    selectItem: function (button_element) {
        var navigation_element = button_element.getParent('.b-blog_nav_sort');
        var item_element = button_element;
        var item_holder_element = item_element.getParent('.b-blog_nav_sort-item');
        var active_item_element = navigation_element.getElement('strong');
        if (active_item_element) {
            var active_item_holder_element = active_item_element.getParent('.b-blog_nav_sort-item');
        }
        if (item_element.tagName.toUpperCase() != 'STRONG') {
            if (active_item_element) {
                var replace_for_active_item_element = new Element('a', {
                    href: active_item_element.getAttribute('href'),
                    'class': 'b-blog_nav_sort_link',
                    html: '<span class="b-blog_nav_sort_link_text">' + active_item_element.innerHTML + '</span>',
                    events: {
                        'click': function (event) {
                            var e = new Event(event)
                            if (window.history && history.pushState) {
                                URIHandler.navigateToPage(replace_for_active_item_element);
                                e.preventDefault();
                            }
                        }
                    }
                });
                replace_for_active_item_element.replaces(active_item_element);
            }
            var replace_for_item_element = new Element('strong', {
                href: item_element.href,
                'class': 'b-blog_nav_sort_link',
                html: item_element.getElement('span').innerHTML
            });
            replace_for_item_element.replaces(item_element);

            if (item_holder_element.getElement('.b-blog_nav_sort_item_info')) {
                item_holder_element.getElement('.b-blog_nav_sort_item_info').removeClass('hidden');
            }
            if (active_item_element && active_item_holder_element.getElement('.b-blog_nav_sort_item_info')) {
                active_item_holder_element.getElement('.b-blog_nav_sort_item_info').addClass('hidden');
            }
        }
    }
};

sortNavigationHandler = {
    selectItem: function (button_element) {
        var navigation_element = button_element.getParent('.b-menu');
        var item_element = button_element.getParent('.b-menu_item');
        var active_item_element = navigation_element.getElement('.b-menu_item_active');
        if (!item_element.hasClass('b-menu_item_active')) {
            if (active_item_element) {
                active_item_element.removeClass('b-menu_item_active');
            }
            item_element.addClass('b-menu_item_active');
        }
    },
    hideNavigation: function () {
        $$('.b-menu__subscriptions').addClass('hidden');
        $$('.b-blog_nav__subscriptions').addClass('b-blog_nav__subscriptions_without_menu');
    },
    showNavigation: function () {
        $$('.b-menu__subscriptions').removeClass('hidden');
        $$('.b-blog_nav__subscriptions').removeClass('b-blog_nav__subscriptions_without_menu');
    },
    deselectAllItems: function (navigation_element) {
        navigation_element.getElements('.b-menu_item_active').removeClass('b-menu_item_active');
    }
};

tagNavigationHandler = {
    selectItem: function (button_element) {
        var navigation_element = button_element.getParent('.b-cloud');
        var item_element = button_element;
        var active_item_element = navigation_element.getElement('.b-cloud_tag_active');
        if (!item_element.hasClass('b-cloud_tag_active')) {
            if (active_item_element) {
                active_item_element.removeClass('b-cloud_tag_active');
            }
            item_element.addClass('b-cloud_tag_active');
        }
    },
    deselectAllItems: function (navigation_element) {
        navigation_element.getElements('.b-cloud_tag_active').removeClass('b-cloud_tag_active');
    }
};

asideNavigationHandler = {
    selectItem: function (button_element) {
        button_element = $(button_element);
        var navigation_element = button_element.getParent('.b-aside_navigation');
        var item_element = button_element.getParent('.b-aside_navigation_item');
        var item_description_element = item_element.getElement('.b-aside_navigation_item_description');
        var active_item_element = navigation_element.getElement('.b-aside_navigation_item_active');
        var active_desription_element = active_item_element.getElement('.b-aside_navigation_item_description');
        var active_button_element = active_item_element.getElement('.b-aside_navigation_item_title');

        var animation_duration = 400;
        if (globals.first_load) {
            animation_duration = 0;
        }

        if (!button_element.getParent('.b-aside_navigation_item_active') && !navigation_element.hasClass('js-animation')) {
            button_element.set('morph', {
                duration: animation_duration / 2
            });
            item_description_element.set('morph', {
                duration: animation_duration
            });

            active_button_element.set('morph', {
                duration: animation_duration
            });
            active_desription_element.set('morph', {
                duration: animation_duration
            });

            navigation_element.addClass('js-animation');

            active_desription_element.get('morph').start({
                'max-height': 0,
                'top': 200,
                'padding-top': 0,
                'padding-bottom': 0
            }).chain(function () {
                active_desription_element.set('styles', {
                    'top': 40
                });
            });

            if (!button_element.getParent('.l-800')) {
                button_element.get('morph').start({
                    'top': 40
                }).chain(function () {
                    button_element.get('morph').start({
                        'background-color': '#556e8c',
                        'padding-top': 4,
                        'top': 0
                    });
                });
            } else {
                button_element.get('morph').start({
                    'background-color': '#556e8c',
                    'padding-top': 4,
                    'top': 0
                });
            }

            item_description_element.get('morph').start({
                'max-height': 300,
                'padding-top': 10,
                'padding-bottom': 20
            });

            if (!button_element.getParent('.l-800')) {
                active_button_element.get('morph').start({
                    'top': 40
                }).chain(function () {
                    active_button_element.get('morph').start({
                        'background-color': '#cdcdcd',
                        'padding-top': 1,
                        'top': 3
                    }).chain(function () {
                        item_element.addClass('b-aside_navigation_item_active');
                        active_item_element.removeClass('b-aside_navigation_item_active');
                        navigation_element.removeClass('js-animation');
                        navigation_element.removeClass('hidden');
                    });
                });
            } else {
                active_button_element.get('morph').start({
                    'background-color': '#cdcdcd',
                    'padding-top': 1,
                    'top': 3
                }).chain(function () {
                    item_element.addClass('b-aside_navigation_item_active');
                    active_item_element.removeClass('b-aside_navigation_item_active');
                    navigation_element.removeClass('js-animation');
                    navigation_element.removeClass('hidden');
                });
            }

        } else {
            navigation_element.removeClass('hidden');
        }
    },
    updateItemsUri: function (mode) {
        $$('.b-aside_navigation_item_title').each(function (item) {
            item.href = '/' + new URI(item.href).get('directory').split('/')[1] + '/';
            if (mode) {
                item.href += mode + '/';
            }
        });
    }
};
notificationHandler = {
    closeContainer: function (container, params) {
        var params = params || {};
        if (container) {
            container.setStyle('overflow', 'hidden');
            var animation = new Fx.Morph(container, {
                duration: 222,
                onComplete: function () {
                    if (params.destroy) {
                        container.destroy();
                    } else {
                        container.addClass('hidden');
                        container.setStyles({
                            height: 'auto'
                        });
                    }
                }.bind(this)
            }).start({
                    opacity: 0,
                    height: params.collapse ? 0 : 'auto'
                });
        }
    },
    showContainer: function (container, callback) {
        if (container) {
            if (container.hasClass('hidden')) {
                if (typeof callback == 'function') {
                    callback.call(this);
                }
                container.removeClass('hidden');
                container.setStyles({
                    opacity: 0,
                    height: 'auto'
                });
                new Fx.Morph(container, {
                    duration: 222
                }).start({
                        opacity: 1
                    });
            } else {
                this.closeContainer(container);
            }
        }
    },
    showEmailForm: function () {
        var container = $('js-email_form'),
            form = container.getElement('form'),
            input = form.getElement('.i-form_text_input'),
            currentEmailEl = $('js-current_email');

        input.value = currentEmailEl.innerHTML.trim();

        if (!this.initEmailSubmitEvent) {
            form.addEvent('submit', function (event) {
                event.preventDefault();
                this.initEmailSubmitEvent = true;
                new futuAjax({
                    button: form.getElement('.b-submit_button'),
                    color_to: '0.5',
                    color_from: '1',
                    attribute: 'opacity',
                    url: ajaxUrls.activation_change_email,
                    data: form.toQueryString(),
                    onLoadFunction: function (response) {
                        new futuAlert('Письмо отправлено');
                        currentEmailEl.innerHTML = input.value.trim();
                        this.closeContainer(container);
                    }.bind(this)
                });
            }.bind(this));
        }
    },
    initUserInfoForm: function () {
        var form = $('js-user_info_form');
        form.addEvent('submit', this.submitUserSettings.bind(this));
    },
    submitUserSettings: function (event) {
        event.preventDefault();

        var form = $('js-user_info_form'),
            birthDay = $('js-register_day').value.trim(),
            birthMonth = $('js-register_month').value.trim(),
            birthYear = $('js-register_year').value.trim();

        var url = ajaxUrls.profile_details;
        var data = form.toQueryString();

        if (birthYear) {
            var date_object = new Date(birthMonth + '/' + birthDay + '/' + birthYear);
            if (date_object) {
                data += '&birthday=' + date_object.format('yyyy-mm-dd');
            }
        }

        new futuAjax({
            button: form.getElement('.b-submit_button'),
            color_to: '#cccccc',
            color_from: '#e9e9e9',
            url: url,
            data: data,
            onLoadFunction: function (response) {
                if (response.birthday) {
                    editProfileHandler.setBirthday(response.birthday);
                }
                new futuAlert('Профайл обновлен');
            }
        });
    },
    // Удаление информационного сообщения
    closeNotification: function (target, message_id, close, animate) {
        var container = target.getParent('.b-notification'),
            close = typeof close !== 'undefined' ? close : true,
            color_to = 0.5;

        if (typeof animate !== 'undefined' && !animate) {
            color_to = 1;
        }

        new futuAjax({
            button: target,
            color_to: color_to,
            color_from: '1',
            attribute: 'opacity',
            url: ajaxUrls.notifications_dismiss,
            data: 'id=' + message_id,
            onLoadFunction: function (response) {
                if (close) {
                    this.closeContainer(container, {
                        destroy: true,
                        collapse: true
                    });
                }
            }.bind(this)
        });
    }
};

URIHandler = {
    navigation_delay_timeout: null,
    navigateToPage: function (button_element) {
        if (!URIHandler.navigation_delay_timeout) {
            URIHandler.navigation_delay_timeout = setTimeout(function () {
                clearTimeout(URIHandler.navigation_delay_timeout);
                URIHandler.navigation_delay_timeout = null;
                URIHandler.parseURI(button_element);
                URIHandler.renderPage();
                globals.first_load = false;
            }, 200);
        }
        if (window.history && history.pushState) {
            if (button_element) {
                var href = null;
                if (button_element.tagName.toLowerCase() == 'form') {
                    href = button_element.action + '?' + $(button_element).toQueryString();
                } else {
                    href = button_element.href;
                }
                if (href) {
                    history.pushState('', button_element.textContent, href);
                }
            }
            return false;
        } else {
            return true;
        }
    },
    parseURI: function (button_element) {
        var uri = null;
        var href = null
        if (button_element) {
            if (button_element.tagName.toLowerCase() == 'form') {
                href = button_element.action + '?' + $(button_element).toQueryString();
            } else {
                href = button_element.href;
            }
            if (href) {
                uri = new URI(href);
            }
        } else {
            uri = new URI(document.location.href);
        }

        var uri_directory = [];
        if (uri.parsed.directory != '/') {
            uri_directory = uri.parsed.directory.substr(1, uri.parsed.directory.length - 2).split('/');
        }
        if (uri.parsed.file.length > 0) {
            uri_directory.push(uri.parsed.file);
        }
        globals.uri_directory = uri_directory;
        globals.uri_directory_to_string = uri.parsed.directory;
        globals.uri_directory_to_string += (uri.parsed.file.length > 0) ? uri.parsed.file + '/' : '';

        globals.uri_query = uri.getData();
    },
    renderPage: function () {
        if (globals.uri_directory.length > 0) {
            switch (globals.uri_directory[0]) {
                case 'blogs':
                    subscriptionsHandler.renderPage();
                    break;
                case 'users':
                    subscriptionsHandler.renderPage();
                    break;
                case 'democracy':
                    promoHandler.renderPage();
                    break;
                default:
            }
        }
    }
};


ajaxHandler = {
    alertError: function (message, errors_quantity, close) {
        var errors_quantity = errors_quantity != undefined ? errors_quantity : 1;
        var close = close != undefined ? close : false;
    },
    checkResponse: function (ajaxObj, text, url) {
        var message = '';
        var close = false;
        var messages_quantity = 0;
        var kl = localMessages.fixed_error_messages.length;
        try {
            if (text) {
                var response = JSON.decode(ajaxObj);
            } else {
                var response = JSON.decode(ajaxObj.responseText);
            }
        } catch (err) {
            ajaxHandler.alertError('<span class="b-futu_alert_error_text">РЎРµСЂРІРµСЂ РѕС‚РІРµС‚РёР» РІ С„РѕСЂРјР°С‚Рµ, РєРѕС‚РѕСЂС‹Р№ РјС‹ РЅРµ РјРѕР¶РµРј СЂР°СЃС€РёС„СЂРѕРІР°С‚СЊ.</span>');
            return false;
        }

        //var response = JSON.decode(ajaxObj);
        if (!$defined(response)) {
            ajaxHandler.alertError('<span class="b-futu_alert_error_text">РЎРµСЂРІРµСЂ РїРѕС‡РµРјСѓ-С‚Рѕ РЅРёС‡РµРіРѕ РЅРµ РѕС‚РІРµС‚РёР».</span>');
            return false;
        }

        if (!response.status) {
            //ajaxHandler.alertError('РЎРµСЂРІРµСЂ РЅРё СЃ С‚РѕРіРѕ, РЅРё СЃ СЃРµРіРѕ СЃРѕРѕР±С‰РёР» СЃР»РµРґСѓСЋС‰РµРµ: В«' + ajaxObj.responseText + 'В»');
            //return false;
        }

        if (response.status == 'ERR') {
            for (var i = 0; i < response.errors.length; i++) {
                messages_quantity++;
                message += localMessages.getErrorMessageByError(response.errors[i], url);
                if (response.errors.length > 1 && i < response.errors.length - 1) {
                    message += '<br>';
                }
                for (var k = 0; k < kl; k++) {
                    if (response.errors[i].code == localMessages.fixed_error_messages[k]) {
                        close = true;
                    }
                }
            }
            if (response.warnings) {
                message += '<br>';
                for (var i = 0; i < response.warnings.length; i++) {
                    messages_quantity++;
                    message += localMessages.getErrorMessageByError(response.warnings[i], url);
                    if (response.warnings.length > 1 && i < response.warnings.length - 1) {
                        message += '<br>';
                    }
                    for (var k = 0; k < kl; k++) {
                        if (response.warnings[i].code == localMessages.fixed_error_messages[k]) {
                            close = true;
                        }
                    }
                }
            }
            ajaxHandler.alertError(message, messages_quantity, close);
            return false;
        }

        if (response.status == 'OK') {
            if (response.warnings) {
                for (var i = 0; i < response.warnings.length; i++) {
                    messages_quantity++;
                    message += localMessages.getErrorMessageByError(response.warnings[i], url);
                    if (response.warnings.length > 1 && i < response.warnings.length - 1) {
                        message += '<br>';
                    }
                    for (var k = 0; k < kl; k++) {
                        if (response.warnings[i].code == localMessages.fixed_error_messages[k]) {
                            close = true;
                        }
                    }
                }
                ajaxHandler.alertError(message, messages_quantity, close);
            }

            if (!response.message) {
                return response;
            } else {
                ajaxHandler.alertError('<span class="b-futu_alert_error_text">' + response.message + '</span>');
                return response;
            }
        } else {
            //ajaxHandler.alertError('РЎРµСЂРІРµСЂ РЅРµ СЃРѕРѕР±С‰РёР» РѕР± РѕС€РёР±РєРµ, РЅРѕ Рё РЅРµ РїРѕРґС‚РІРµСЂРґРёР», С‡С‚Рѕ РІСЃС‘ РїСЂРѕС€Р»Рѕ С…РѕСЂРѕС€Рѕ.');
            return response;
        }
    },
    highlightField: function (input, bg_color, highlight_color, attribute) {
        var input = $(input);
        var bg_color = bg_color || Colors.inputs_bg_color;
        var highlight_color = highlight_color || '#FF0000';
        var attribute = attribute || 'backgroundColor';
        var loadingHighlightFx = new Fx.Tween($(input), {'link': 'cancel', 'onComplete': function () {
            $(input).erase('style');
            $(input).removeClass('js-highlighting_field');
        }});
        if (!$(input).hasClass('js-highlighting_field')) {
            $(input).addClass('js-highlighting_field');
            //input.style.backgroundColor = highlight_color;
            loadingHighlightFx.start(attribute, highlight_color, bg_color);
        }
    },
    loadingHighlight: function (params) {
        if (!params.color1)
            params.color1 = '#cccccc';
        if (!params.color2)
            params.color2 = '#e9e9e9';
        var button = $(params.button);
        params.attribute = params.attribute || 'background-color';
        params.timing = params.timing || 333;
        var fx = new Fx.Tween($(button), {'duration': params.timing, 'onComplete': function () {
            if ($(button).hasClass('js-lh_to_color_2')) {
                fx.start(params.attribute, params.color1, params.color2);
                $(button).removeClass('js-lh_to_color_2');
            } else {
                if (params.loadingCheck()) {
                    //fx.start(params.attribute, params.color2, params.color1);
                    $(button).addClass('js-lh_to_color_2');
                } else {
                    $(button).removeClass('js-lh_active');
                }
            }
        }});
        if ($(button).hasClass('js-lh_active')) {
            (function () {
                if (params.loadingCheck()) {
                    //fx.start(params.attribute, params.color2, params.color1);
                    $(button).addClass('js-lh_to_color_2');
                    $(button).addClass('js-lh_active');
                }
            }).delay(params.timing);
        } else {
            //fx.start(params.attribute, params.color2, params.color1);
            $(button).addClass('js-lh_to_color_2');
            $(button).addClass('js-lh_active');
        }
        return fx;
    }
};

futuAjax = new Class({
    Implements: Options,
    options: {
        button: false,
        animated_element: false,
        remove_element_color: true,
        loading_class: 'js-loading',
        attribute: 'background-color',
        dont_stop_animation: false,
        color_to: '#FFFFFF',
        color_from: '#EDF14B',
        url: '/ajax/',
        data: '',
        type: 'POST',
        checkResponseFunction: ajaxHandler.checkResponse,
        onErrorFunction: false,
        onCustomErrorFunction: $empty,
        onBeforeLoadFunction: $empty,
        onLoadFunction: $empty,
        //alertFunction : futuAlert,
        checkAjaxLoadedFunction: function () {
            return this.button.hasClass(this.loading_class);
        },
        setAjaxLoadingFunction: function () {
            this.button.addClass(this.loading_class);
        },
        removeAjaxLoadingFunction: function () {
            this.button.removeClass(this.loading_class);
        }
    },
    initialize: function (options) {
        this.setOptions(options);
        this.button = this.options.button;
        this.loading_class = this.options.loading_class;
        this.animated_element = this.options.animated_element || this.button;
        this.remove_element_color = this.options.remove_element_color;
        this.color_to = this.options.color_to;
        this.color_from = this.options.color_from;
        this.url = this.options.url;
        this.data = this.options.data;
        this.type = this.options.type;
        this.checkResponseFunction = this.options.checkResponseFunction;
        this.onErrorFunction = this.options.onErrorFunction;
        this.onCustomErrorFunction = this.options.onCustomErrorFunction;
        this.onLoadFunction = this.options.onLoadFunction;
        this.alertFunction = this.options.alertFunction;
        if (!this.button) {
            new this.alertFunction('РќРµ СѓРєР°Р·Р°РЅР° РѕР±СЏР·Р°С‚РµР»СЊРЅР°СЏ РєРЅРѕРїРєР°.');
        }
        if (this.button && !this.options.checkAjaxLoadedFunction()) {
            (this.options.setAjaxLoadingFunction).bind(this).call();
            (this.options.onBeforeLoadFunction).bind(this).call();
            this.loading_animation_fx = ajaxHandler.loadingHighlight({'button': this.animated_element,
                'color1': this.color_to,
                'color2': this.color_from,
                'attribute': this.options.attribute,
                'loadingCheck': (this.options.checkAjaxLoadedFunction).bind(this),
                'transparent': this.remove_element_color
            });
            this.postAjax(this.url, this.data, this.type, (function (ajaxObj) {
                if (!this.options.dont_stop_animation) {
                    (this.options.removeAjaxLoadingFunction).bind(this).call();
                }
                var response = this.checkResponseFunction(ajaxObj, false, this.url);
                if (response) {
                    this.onLoadFunction(response);
                } else {
                    response = JSON.decode(ajaxObj.responseText);
                    this.onCustomErrorFunction(response);
                }
            }).bind(this), this);
        }
    },
    postAjax: function (url, data, type, ajaxCallBackFunction) {
        var parseData = data.parseQueryString();

        var ajaxObject = null;
        if (window.XMLHttpRequest) { // branch for native XMLHttpRequest object
            ajaxObject = new XMLHttpRequest();
        } else if (window.ActiveXObject) { // branch for IE/Windows ActiveX version
            var ajaxObject = new ActiveXObject("Microsoft.XMLHTTP");
        }
        if (ajaxObject) {
            ajaxObject.onreadystatechange = (function () {
                // only if req shows "complete"
                if (ajaxObject.readyState == 4) {
                    // only if "OK"
                    if (ajaxObject.status == 200) {
                        // ...processing statements go here...
                        ajaxCallBackFunction.call(this, ajaxObject);
                    } else {
                        if (ajaxObject.status > 0) {
                            if (this.onErrorFunction) {
                                this.onErrorFunction(ajaxObject);
                            }

                        }
                        (this.options.removeAjaxLoadingFunction).bind(this).call();
                    }
                }
            }).bind(this);

            if (type == 'GET') {
                url += data != '' ? '?' + data : '';
            }

            ajaxObject.open(type, url, true);
            try {
                ajaxObject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                //ajaxObject.setRequestHeader("Content-length", data.length);
                //ajaxObject.setRequestHeader("Connection", "close");
            } catch (error) {

            }
            ajaxObject.send(data);
        }
    }
});

var KarmaHandler = new Class({
    limit: {
        left: {
            x: [0, 28]
        },
        right: {
            x: [-29, 0]
        }
    },
    preventClick: false,

    initialize: function (voteResultsHandler, currentVote) {
        this.mainContainer = $$('.js-karma')[0];
        this.containers = $$('.js-karma_controls');
        this.voteResultsHandler = voteResultsHandler;
        this.currentVote = currentVote;
        this.isVoted = this.mainContainer.hasClass('js-voted');

        Array.each(this.containers, function (el, i) {
            this.dragInit(el);
            el.addEvent('mouseup', function (event) {
                var sendRequest;

                if (!this.mainContainer.hasClass('js-voted')) {
                    this.mainContainer.addClass('js-voted')
                }

                if (!this.preventClick) {
                    sendRequest = !el.hasClass('b-voted_2');
                    this.animateControls(el, function () {
                        if (sendRequest) {
                            this.handleKarmaValue(el);
                        }
                    }.bind(this));
                }
            }.bind(this));
        }.bind(this));
    },

    // Инициализация класса Drag для контейнера с контролами
    dragInit: function (el) {
        var currentElModifier,
            secondElModifier,
            index,
            k;

        if (el.hasClass('left')) {
            currentElModifier = 'left';
            secondElModifier = 'right';
            index = 0;
            k = -1;
        } else {
            currentElModifier = 'right';
            secondElModifier = 'left';
            index = 1;
            k = 1;
        }

        var drag = new Drag(el, {
            limit: this.limit[currentElModifier],
            modifiers: {x: 'left', y: false},
            preventDefault: true,
            onStart: function () {
                this.preventClick = true;

                if (!this.mainContainer.hasClass('js-voted')) {
                    this.mainContainer.addClass('js-voted')
                }
            }.bind(this),
            onDrag: function (el) {
                var secondEl = this.containers.filter('.' + secondElModifier),
                    currentSecondElPosition = parseInt(secondEl.getStyle('left'), 10),
                    newPosition = this.limit[secondElModifier].x[index] + parseInt(el.getStyle('left'), 10) + 8 * k;

                if (Math.abs(newPosition) > Math.abs(currentSecondElPosition) && currentSecondElPosition != this.limit[secondElModifier].x[index]) {
                    secondEl.setStyle('left', Math.abs(newPosition) < Math.abs(this.limit[secondElModifier].x[index]) ? newPosition : this.limit[secondElModifier].x[index]);
                }
            }.bind(this),
            onSnap: function () {
                if (el.hasClass('b-voted_2')) {
                    drag.stop();
                    this.preventClick = false;
                }
            }.bind(this),
            onComplete: function (el, event) {
                this.animateControls(el, function () {
                    this.handleKarmaValue(el);
                }.bind(this));
                this.preventClick = false;
            }.bind(this)
        });
    },

    slideControls: function (el, position, callback) {
        new Fx.Morph(el, {
            duration: 100,
            transition: Fx.Transitions.Sine.easeOut,
            onComplete: function () {
                if (typeof callback === 'function') {
                    callback();
                }
            }
        }).start({
                left: position
            });
    },

    animateControls: function (el, callback) {
        var currentElModifier,
            secondElModifier,
            currentEl,
            secondEl,
            currentElPosition,
            secondElPosition,
            newCurrentElPosition,
            newSecondElPosition;

        if (el.hasClass('right')) {
            currentElModifier = 'right';
            secondElModifier = 'left';
            currentEl = this.containers.filter('.' + currentElModifier);
            secondEl = this.containers.filter('.' + secondElModifier);
            currentElPosition = parseInt(currentEl.getStyle('left'), 10);
            secondElPosition = parseInt(secondEl.getStyle('left'), 10);

            if (currentElPosition < -25) {
                newCurrentElPosition = -25;
                newSecondElPosition = 14;
            } else if (currentElPosition < -19) {
                newCurrentElPosition = -19;
                newSecondElPosition = 19;
            } else if (currentElPosition < -14) {
                newCurrentElPosition = -14;
                newSecondElPosition = 25;
            } else if (currentElPosition < 0) {
                newCurrentElPosition = 0;
                newSecondElPosition = 28;
            } else {
                newCurrentElPosition = currentElPosition;
                newSecondElPosition = secondElPosition;
            }

            this.setControlsParams(currentEl, secondEl, newCurrentElPosition, newSecondElPosition, -25, -19, -14, 0);
        } else if (el.hasClass('left')) {
            currentElModifier = 'left';
            secondElModifier = 'right';
            currentEl = this.containers.filter('.' + currentElModifier);
            secondEl = this.containers.filter('.' + secondElModifier);
            currentElPosition = parseInt(currentEl.getStyle('left'), 10);
            secondElPosition = parseInt(secondEl.getStyle('left'), 10);

            if (currentElPosition > 25) {
                newCurrentElPosition = 25;
                newSecondElPosition = -14;
            } else if (currentElPosition > 19) {
                newCurrentElPosition = 19;
                newSecondElPosition = -19;
            } else if (currentElPosition > 14) {
                newCurrentElPosition = 14;
                newSecondElPosition = -25;
            } else if (currentElPosition > 0) {
                newCurrentElPosition = 0;
                newSecondElPosition = -29;
            } else {
                newCurrentElPosition = currentElPosition;
                newSecondElPosition = secondElPosition;
            }

            this.setControlsParams(currentEl, secondEl, newCurrentElPosition, newSecondElPosition, 25, 19, 14, 0);
        }

        this.slideControls(currentEl[0], newCurrentElPosition, callback);

        if (Math.abs(secondElPosition) < Math.abs(newSecondElPosition)) {
            this.slideControls(secondEl[0], newSecondElPosition, callback);
        }
    },

    // Проставление соответсвующих классов и data-атрибутов обоим контролам
    // в зависимости от позиции смещения
    setControlsParams: function (currentEl, secondEl, currentElPos, secondElPos, pos1, pos2, pos3, pos4) {
        var secondElValue = secondEl.get('data-value');

        if (currentElPos == pos1) {
            currentEl
                .set('class', this.replaceClassName(currentEl[0], 'b-voted_0'))
                .set('data-value', 0);
            secondEl
                .set('class', this.replaceClassName(secondEl[0], 'b-voted_1'))
                .set('data-value', 1);
        } else if (currentElPos == pos2) {
            currentEl
                .set('class', this.replaceClassName(currentEl[0], 'b-voted_0'))
                .set('data-value', 0);
            secondEl
                .set('class', this.replaceClassName(secondEl[0], 'b-voted_0'))
                .set('data-value', 0);
        } else if (currentElPos == pos3) {
            currentEl
                .set('class', this.replaceClassName(currentEl[0], 'b-voted_1'))
                .set('data-value', 1);
            secondEl
                .set('class', this.replaceClassName(secondEl[0], 'b-voted_0'))
                .set('data-value', 0);
        } else if (currentElPos == pos4) {
            currentEl
                .set('class', this.replaceClassName(currentEl[0], 'b-voted_2'))
                .set('data-value', 2);
            secondEl
                .set('class', this.replaceClassName(secondEl[0], 'b-voted_0'))
                .set('data-value', 0);
        }
    },

    handleKarmaValue: function (el) {
        var leftControls = this.containers.filter('.left'),
            rightControls = this.containers.filter('.right'),
            valueContainer = this.mainContainer.getElement('.b-karma_value'),
            secondElModifier,
            currentControlClass,
            secondControlClass,
            secondEl,
            leftElValue,
            rightElValue,
            controlsValue;

        if (el.hasClass('left')) {
            secondElModifier = 'right';
        } else {
            secondElModifier = 'left';
        }

        secondEl = this.containers.filter('.' + secondElModifier)[0];
        currentControlClass = el.className;
        secondControlClass = secondEl.className;
        leftElValue = leftControls.get('data-value');
        rightElValue = rightControls.get('data-value');

        if (leftElValue == 0 && rightElValue == 0) {
            controlsValue = '0';
        } else if (rightElValue > 0) {
            controlsValue = rightElValue;
        } else if (leftElValue > 0) {
            controlsValue = -leftElValue;
        }


        var _this = this;
        window.clearTimeout(this.send_karma_timeout);
        this.send_karma_timeout = null;
        this.send_karma_timeout = window.setTimeout(_this.sendKarmaValue, 700, el, valueContainer, controlsValue, _this.setDefaultState);
    },


    sendKarmaValue: function (el, valueContainer, controlsValue) {
        $('.b-karma_controls-container.js-karma_controls.left').animate({left: '19px'}, 100);
        $('.b-karma_controls-container.js-karma_controls.right').animate({left: '-19px'}, 100);
    },

    setDefaultState: function () {
        var leftContainer = this.containers.filter('.left')[0],
            rightContainer = this.containers.filter('.right')[0],
            leftContainerValue,
            rightContainerValue;

        if (this.currentVote == 0) {
            leftContainerValue = 0;
            rightContainerValue = 0;
        } else if (this.currentVote == -2) {
            leftContainerValue = 2;
            rightContainerValue = 0;
        } else if (this.currentVote == -1) {
            leftContainerValue = 1;
            rightContainerValue = 0;
        } else if (this.currentVote == 1) {
            leftContainerValue = 0;
            rightContainerValue = 1;
        } else if (this.currentVote == 2) {
            leftContainerValue = 0;
            rightContainerValue = 2;
        }
        leftContainer
            .set('class', this.replaceClassName(leftContainer, 'b-voted_' + leftContainerValue))
            .set('data-value', leftContainerValue);
        rightContainer
            .set('class', this.replaceClassName(rightContainer, 'b-voted_' + rightContainerValue))
            .set('data-value', rightContainerValue);
        leftContainer.erase('style');
        rightContainer.erase('style');
        if (!this.isVoted) {
            this.mainContainer.removeClass('js-voted');
        }
    },

    replaceClassName: function (el, value) {
        return el.className.replace(new RegExp('b-voted_[0-9]+'), value);
    }
});
tagsHandler = {
    tags: [],
    init: function (options) {
        this.input = options.input;
        this.container = options.container;
        this.hiddenListInput = options.hiddenListInput;
        this.saveUrl = options.saveUrl || false;
        this.type = options.type || '';
        this.onChange = options.onChange || (function () {
        });

        this.addDeleteEvent();
        this.setTagsList();
        this.initTagsAdding();
    },

    // Добавление тегов в список
    initTagsAdding: function (options) {
        this.input.addEvent('keydown', function (event) {
            if (event.code == 13) {
                event.preventDefault();
            }
        });
        this.input.addEvent('keyup', function (event) {
            if (!this.input.hasClass('js-loading')) {
                var tags_element_value = this.input.value.trim();

                if (this.type == 'domain') {
                    if (event.code == 13 || event.code == 32) {
                        this.input.value = '';
                        // Валидация тегов
                        new futuAjax({
                            button: this.input,
                            color_to: Colors.background_color,
                            color_from: Colors.inputs_bg_color,
                            url: ajaxUrls.domain_tag_validate,
                            data: 'tags=' + encodeURIComponent(tags_element_value),
                            onLoadFunction: function (response) {
                                this.addTagsToList(response);
                                if (this.saveUrl) {
                                    this.saveTags(this.input);
                                }
                            }.bind(this),
                            onCustomErrorFunction: function (response) {
                                this.addTagsToList(response);
                                if (this.saveUrl) {
                                    this.saveTags(this.input);
                                }
                            }.bind(this)
                        });
                    }
                } else {
                    if (event.code == 13) {
                        this.input.value = '';
                        var response = {
                            tags: tags_element_value.split(',')
                        };
                        response.tags = response.tags.map(function (tag) {
                            return tag.trim()
                        });

                        this.addTagsToList(response);
                        if (this.saveUrl) {
                            this.saveTags(this.input);
                        }
                    }
                }
            }
        }.bind(this))
    },

    // Добавление проверенных сервером тегов

    addTagsToList: function (response) {
        for (var i = 0, l = response.tags.length; i < l; i++) {
            var text = response.tags[i],
                isNewTag = true;

            for (var j = 0, l2 = this.tags.length; j < l2; j++) {
                if (this.tags[j] == text) {
                    isNewTag = false;
                }
            }

            if (isNewTag) {
                var item = new Element(' li', {
                        'html': '<span class="tag">\
							<a href="#" class="b-controls_button b-fui_icon_button_close"><span></span></a>\
							<span class="b-tag_text">{name}</span>\
						</span>&nbsp;'.substitute({
                                name: text
                            })
                    }),
                    removeButton = item.getElement('.b-fui_icon_button_close');

                item.inject(this.container);

                removeButton.addEvent('click', function (event) {
                    event.preventDefault();
                    var button = event.target,
                        item = button.getParent('li');
                    this.removeTag(item, !!this.saveUrl);
                }.bind(this));
            }
        }

        this.setTagsList();
        this.onChange();
    },

    // Заполнение тегами массива и скрытого инпута
    setTagsList: function () {
        var items = this.container.getElements('.b-tag_text');
        this.hiddenListInput.value = '';
        this.tags = [];

        for (var i = 0, l = items.length; i < l; i++) {
            this.tags.push(items[i].innerHTML.trim());
        }
        this.hiddenListInput.value = this.tags.join(', ');
    },

    // Навешивание обработчика события клика для удаления тега
    addDeleteEvent: function () {
        var removeButtons = this.container.getElements('.b-fui_icon_button_close');

        for (var i = 0, l = removeButtons.length; i < l; i++) {
            removeButtons[i].addEvent('click', function (event) {
                event.preventDefault();
                var button = event.target,
                    item = button.getParent('li');

                this.removeTag(item, !!this.saveUrl);
            }.bind(this));
        }
    },

    // Удаление тега из массива тегов, скрытого инпута и DOM элемента
    removeTag: function (item, sendRequest) {
        var text = item.getElement('.b-tag_text').innerHTML.trim();

        for (var i = 0, l = this.tags.length; i < l; i++) {
            if (this.tags[i] === text) {
                this.tags.splice(i, 1);
                this.hiddenListInput.value = this.tags.join(', ');
                if (sendRequest) {
                    this.saveTags(item, function () {
                        item.destroy();
                    })
                } else {
                    item.destroy();
                }

            }
        }

        this.onChange();
    },

    saveTags: function (item, callback) {
        new futuAjax({
            button: item,
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: this.saveUrl,
            data: 'tags=' + this.hiddenListInput.value.trim(),
            onLoadFunction: function (response) {
                if (callback && typeof callback === 'function') {
                    callback();
                }
            }.bind(this),
            onCustomErrorFunction: function (response) {
                this.removeTag(item);
            }.bind(this)
        });
    },

    goToTagPosts: function () {
        if ($('js-tagname_input').value.trim().length < 1) {
            new futuAlert('Не забудьте написать что-нибудь в поле для тега.');
            ajaxHandler.highlightField($('js-tagname_input'));
        } else {
            window.location.href = "/tag/" + encodeURIComponent($('js-tagname_input').value);
        }
    },
    showMoreTags: function (button, tags_more_element_id) {
        var tags_more_element = $(tags_more_element_id);
        tags_more_element.toggleClass('hidden');
    }
};
newPostHandler = {
    customLoadingColor1: '#cccccc',
    customLoadingColor2: '#e9e9e9',
    customLoadingColor3: '#cccccc',
    customLoadingColor4: '#e9e9e9',
    current_type: null,
    saving_draft_timeout: null,
    post_type: null, // тип редактируемого поста
    post_id: null,

    init: function (type, post_id) {
        this.form_content = $$('.js-form_content')[0];
        newPostHandler.current_type = type;
        this.subscribed_list_control = $$('.b-more_button_subscribed')[0];
        this.preview_control = $$('.b-new_post_preview_link_holder')[0];
        this.post_preview_container = $('js-post_preview');
        this.post_preview_content = this.post_preview_container.getElement('.b-post_preview');
        this.post_submit_container = $('js-post_submit');
        this.menu_items = $$('.b-side_menu_item');
        this.draft_time_container = $('js-draft_time');

        if (post_id) {
            this.post_id = post_id;
        }
        newPostHandler.toggleForm(newPostHandler.current_type);

        var uri = new URI(document.location.href);
        if (uri.parsed.directory == '/edit/') {
            this.post_type = type;
        }

        if (this.subscribed_list_control) {
            this.subscribed_list_control.addEvent('click', this.toggleSubscribedList.bind(this));
            window.addEvent('resize', this.setSubscribedDomainsListStyle.bind(this));
        }

        var menu_links = $$('.b-side_menu_item .b-side_menu_nav_link');
        menu_links.addEvent('click', function (event) {
            event.preventDefault();

            var target = event.target,
                parent = target.getParent('.b-side_menu_item');

            if (!parent.hasClass('active')) {
                this.toggleForm(parent.get('data-type'));
                if (window.history && history.pushState) {
                    history.pushState('', target.innerHTML, target.href);
                    return false;
                }
            }
        }.bind(this));
        window.addEventListener('popstate', function (event) {
            var uri = new URI(document.location.href),
                type = uri.parsed.directory.split('/')[2];

            if (type.trim() == '') {
                type = this.post_type ? this.post_type : 'post';
            }

            if (this.current_type != type) {
                this.toggleForm(type);
            }
        }.bind(this));
    },

    toggleForm: function (type) {
        var el = $('js-form_fields_' + type).getElement('.js-form_piece'),
            currentElContainer = $('js-form_fields_' + newPostHandler.current_type),
            currentEl = $('js-new_post_form').getElement('.js-form_piece');

        if (currentEl) {
            currentEl.inject(currentElContainer);
        }

        el.inject(newPostHandler.form_content);

        newPostHandler.current_type = type;
        $(document.body)
            .removeClass('b-post_page_post')
            .removeClass('b-post_page_article')
            .removeClass('b-post_page_drafts')
            .removeClass('b-post_page_gallery')
            .addClass('b-post_page_' + type);

        // Удаление содержимого предпросмотра
        this.post_preview_content.empty();
        $('js-new_post_render_types').addClass('hidden');

        if (type == 'post') {
            // Раскрытие кнопки сабмита формы в режиме поста-ссылки
            this.post_submit_container.removeClass('hidden');
            this.setSubscribedDomainsListStyle();
        } else if (type == 'gallery') {
            // Раскрытие кнопки сабмита формы в режиме поста-галереи
            this.post_submit_container.removeClass('hidden');
            el.getParent('.b-post_fields_wrapper').addClass('b-post_fields_wrapper_gallery');
            this.setSubscribedDomainsListStyle();
        } else {
            // Скрытие превью статьи и раскрытие кнопки сабмита в режиме поста-статьи
            this.post_preview_container.addClass('hidden');
            this.post_submit_container.removeClass('hidden');
            this.setSubscribedDomainsListStyle();
        }

        this.menu_items.each(function (item) {
            if (item.get('data-type') == type && !item.hasClass('active')) {
                item
                    .addClass('active')
                    .getSiblings()
                    .removeClass('active');
            }
        });
    },

    validateNewPost: function (params) {
        // подсайта нет
        var new_domain_element = $('js-new_post_domain');
        var selected_index_element = new_domain_element.getElement('.b-new_post_domain_selected_index');
        var selected_domain_id_input = $$('.js-new_post_domain_selected')[0];
        var selected_domain_query_input = new_domain_element.getElement('.b-new_post_domain_selected_subdomain input');

        // для черновиков мы не проверяем заполненность полей
        if (params.type == 'draft') {
            return true;
        }
        if (!params.preview && params.type != 'draft') {
            if (selected_index_element.hasClass('hidden') && (selected_domain_id_input.value == '' || selected_domain_query_input.value.trim().length == 0)) {
                new futuAlert('К сожалению, мы не нашли указанный вами домен.');
                return false;
            }
        }

        if (newPostHandler.current_type == 'post') {
            var image_input = $('js-new_post_media_id');
            if (!params.preview) {
                if ($('js-new_post_header').value.trim().length < 1) {
                    new futuAlert('Заголовок у поста теперь обязателен.');
                    $('js-new_post_header').focus();
                    return false;
                }
            }
            if ($('js-new_post_body').value.trim().length < 1 && $('js-new_post_url').value.trim().length < 1 && (!image_input || image_input.value.trim().length < 1)) {
                $('js-new_post_url').focus();
                new futuAlert('Пост должен содержать ссылку или текст, или изображение&nbsp;&mdash; одно из трёх или всё сразу.');
                return false;
            }
        } else if (newPostHandler.current_type == 'article') {
            if (futuEditor.getContentEditableContent($('js-new_post_title')).length < 1) {
                new futuAlert('Заголовок у поста теперь обязателен.');
                $('js-new_post_title').focus();
                return false;
            }
            if (futuEditor.validateFields()) {
                new futuAlert('С такой подачей у нас с вами приличного поста не получится.');
                return false;
            }
        } else if (newPostHandler.current_type == 'gallery') {
            var gallery_container = $('js-gallery_images_list'),
                images = gallery_container.getElements('.b-gallery_image_thumb');
            if (futuEditor.getContentEditableContent($('js-new_post_gallery_title')).length < 1) {
                new futuAlert('Придумайте название галереи.');
                $('js-new_post_gallery_title').focus();
                return false;
            }
            if (images.length == 0) {
                new futuAlert('Вы не загрузили ни одного изображения в галерею.');
                return false;
            }
            if (images.length == 1) {
                new futuAlert('Галерея должна состоять из нескольких изображений.');
                return false;
            }
        }
        return true;
    },
    submitNewPost: function (type) {
        setTimeout(function () {
            var data,
                url,
                wysiwyg_data,
                button;

            if (newPostHandler.validateNewPost({type: type})) {
                //var post_id_input = $('js-post_id');

                if (type == 'draft') {
                    data = 'tags=' + $('js-tags_list').value;
                } else {
                    data = $('js-new_post_form').toQueryString();
                }

                if (this.current_type == 'article' || this.current_type == 'gallery') {
                    wysiwyg_data = JSON.encode(futuEditor.serializeToJSON(this.current_type));
                } else {
                    wysiwyg_data = JSON.encode(this.serializeToJSON());
                }
                data += '&wysiwyg_data=' + encodeURIComponent(wysiwyg_data);

                // Редактирование черновика
                if (this.post_id) {
                    data += '&post=' + this.post_id;
                    if (this.current_type == 'article') {
                        url = ajaxUrls.post_article_edit;
                    } else if (this.current_type == 'gallery') {
                        url = ajaxUrls.post_gallery_edit;
                    } else if (this.current_type == 'post') {
                        url = ajaxUrls.post_link_edit;
                    }
                } else {
                    // Создание нового поста/черновика
                    if (this.current_type == 'post') {
                        url = ajaxUrls.post_link_submit;
                    } else {
                        url = this.current_type == 'article' ? ajaxUrls.post_article_submit : ajaxUrls.post_gallery_submit;
                    }
                }

                if (type == 'draft') {
                    button = this.draft_time_container.getElement('.b-draft_submit');
                } else {
                    button = $('js-new_post_submit').getElement('.b-new_post_form_submit_button');
                }
                new futuAjax({
                    button: button,
                    color_to: 0.5,
                    color_from: 1,
                    attribute: 'opacity',
                    url: url,
                    data: data,
                    onLoadFunction: function (response) {
                        if (type == 'draft') {
                            this.post_id = response.post.id;
                            this.draft_time_container.getElement('.b-draft_saved').innerHTML = 'Пост сохранен <span class="js-date js-date-regular-date-time"></span> ·';
                            this.draft_time_container.getElement('.b-draft_submit').innerHTML = 'сохранить?';
                            this.draft_time_container.getElement('.js-date')
                                .removeClass('js-date__formatted')
                                .set('data-epoch_date', response.post.created);
                            datesHandler.setDates();

                            var nav_element = $$('.b-side_menu')[0];
                            if (!nav_element.hasClass('js-nav_in_drafts')) {
                                nav_element.addClass('js-nav_in_drafts');
                                if (window.history && history.pushState) {
                                    var href = '/edit/' + this.post_id;
                                    history.pushState('', 'd3.ru — Редактирование поста', href);
                                    //nav_element.getElements('.b-side_menu_item').addClass('hidden');
                                    //nav_element.getElement('.active').removeClass('hidden');
                                    //nav_element.getElements('.b-side_menu_item').getLast().removeClass('hidden');
                                }
                            }

                        } else {
                            // Публикование нового поста без сохранения в черновик
                            if (!this.post_id) {
                                window.location.href = 'http://' + response.post.domain.url + '/comments/' + response.post.id;
                            } else {
                                // Пост был отредактирован модератором
                                if (response.post.user.id != globals.user.id) {
                                    window.location.href = 'http://' + response.post.domain.url + '/comments/' + response.post.id;
                                } else {
                                    // Черновик был обновлен и теперь его нужно опубликовать
                                    new futuAjax({
                                        button: button,
                                        color_to: 0.5,
                                        color_from: 1,
                                        attribute: 'opacity',
                                        url: ajaxUrls.post_publish,
                                        data: data,
                                        onLoadFunction: function (response) {
                                            window.location.href = 'http://' + response.post.domain.url + '/comments/' + response.post.id;
                                        }
                                    });
                                }
                            }
                        }
                    }.bind(this)
                });
            }
        }.bind(this), 100);
    },

    previewPost: function () {
        setTimeout(function () {
            if (newPostHandler.validateNewPost({preview: true})) {
                var data = $('js-new_post_form').toQueryString(),
                    wysiwyg_data,
                    url;
                if (this.current_type == 'post') {
                    url = ajaxUrls.post_link_preview;
                    wysiwyg_data = JSON.encode(this.serializeToJSON());
                } else if (this.current_type == 'gallery') {
                    url = ajaxUrls.post_gallery_preview;
                    wysiwyg_data = JSON.encode(futuEditor.serializeToJSON(this.current_type));
                }
                data += '&wysiwyg_data=' + encodeURIComponent(wysiwyg_data);
                new futuAjax({
                    button: $$('.b-new_post_preview_link')[0],
                    attribute: 'opacity',
                    color_to: 0.5,
                    color_from: 1,
                    url: url,
                    data: data,
                    onLoadFunction: function (response) {
                        var content = JSON.decode(response.wysiwyg_data);
                        // set post preview
                        if (this.current_type == 'post') {

                            // html entities to text
                            if (content.title) {
                                var temp_textarea = document.createElement("textarea");
                                temp_textarea.innerHTML = content.title;
                                $('js-new_post_header').value = temp_textarea.value;
                            }

                            this.post_preview_content.innerHTML = response.template;

                            // init audio
                            audioHandler.init();

                            futuPics.initExpandingPics();
                        } else if (this.current_type == 'gallery') {
                            var gallery = content.body[0].body;
                            var gallery_container = new Element('div', {
                                html: $('js-gallery_template').innerHTML
                            });
                            var thumbnails_container = gallery_container.getElement('.b-gallery_thumbnails_inner');
                            for (var i = 0, l = gallery.length; i < l; i++) {
                                var el = new Element('a', {
                                    'class': 'b-gallery_thumbnail',
                                    'data-description': gallery[i].content,
                                    href: gallery[i].image,
                                    html: '<img class="b-gallery_image_thumb" alt="" src="' + gallery[i].preview + '"><i class="b-gallery_thumb_border"></i>'
                                });
                                el.inject(thumbnails_container);
                            }

                            this.post_preview_content.empty();
                            gallery_container.inject(this.post_preview_content);
                            new Gallery($('js-post_preview').getElement('.b-gallery_container'));
                        }

                        this.post_preview_container.removeClass('hidden');
                        this.post_submit_container.removeClass('hidden');
                        this.setSubscribedDomainsListStyle();
                    }.bind(this)
                });
            } else {
                //this.post_submit_container.addClass('hidden');
                $('js-new_post_render_types').addClass('hidden');
            }
        }.bind(this), 100);
    },
    submitDraft: function (delay) {
        var delay = delay != undefined ? delay : 1000;
        if (this.saving_draft_timeout) {
            clearTimeout(this.saving_draft_timeout);
        }
        this.saving_draft_timeout = setTimeout(function () {
            var wysiwyg_data = JSON.encode(futuEditor.serializeToJSON(this.current_type));
            newPostHandler.submitNewPost('draft');
        }.bind(this), delay);
    },
    deleteDraft: function (post_id) {
        var item = $('js-draft_item_' + post_id),
            button = item.getElement('.b-fui_icon_button_close'),
            list = $$('.b-drafts_list')[0];

        new futuAjax({
            button: button,
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.post_wysiwyg_delete,
            data: 'post=' + post_id,
            onLoadFunction: function (response) {
                new Fx.Morph(item, {
                    duration: 200,
                    onComplete: function () {
                        item.destroy();
                        if (list.getElements('.b-draft_item').length == 0) {
                            list.innerHTML = '<div class="b-drafts_empty">У вас нет сохраненных черновиков</div>'
                        }
                    }.bind(this)
                }).start({
                        opacity: 0
                    });
            }
        });
    },
    dropTag: function (button) {
        $(button).getParent('li').destroy();
        if (!$('js-post_preview_tags').getElement('li')) {
            $('js-post_preview_tags').addClass('hidden');
            $('js-new_post_tags').value = '';
        } else {
            var tags_input_value = '';
            $A($('js-post_preview_tags').getElements('.tag')).each(function (tag_element) {
                tags_input_value += tag_element.innerHTML + ' '
            });
            $('js-new_post_tags').value = tags_input_value;
        }
    },
    initFileUploader: function () {
        if (!utils.isFileUploadSupported()) {
            $('js-new_post_file_field').addClass('hidden');
            return;
        }
        var dragContainer = $('js-file_uploader_drag');
        new futuFileUploader({
            container: 'js-file_uploader',
            browseButton: 'js-file_uploader_button',
            dropElement: 'js-file_uploader_drag',
            dropElementNode: dragContainer,
            uploadProgress: function (up, file) {
                dragContainer.innerHTML = file.name + '&nbsp;(' + file.percent + '%)';
            }.bind(this),
            uploadComplete: function (up, file, response) {
                ajaxHandler.highlightField($('js-file_uploader'), '#FFFFFF', '#556E8C');
            }.bind(this),
            fileUploaded: function (up, file, response) {
                var response = JSON.decode(response.response);
                if (response.media_id) {
                    var file_uploading_container = $('js-new_post_file_field'),
                        uploaded_image_container = file_uploading_container.getElement('.b-post_edit_image')
                    if ($('js-new_post_media_id')) {
                        $('js-new_post_media_id').value = response.media_id;
                    } else {
                        var media_input = new Element('input', {
                            type: 'hidden',
                            name: 'media',
                            id: 'js-new_post_media_id',
                            value: response.media_id
                        });
                        media_input.inject(file_uploading_container);
                    }

                    file_uploading_container.getElement('.b-new_post_image_delete_file').removeClass('hidden');
                    if (uploaded_image_container) {
                        uploaded_image_container.destroy();
                    }

                    newPostHandler.submitDraft();
                } else {
                    if (response.status == 'ERR') {
                        for (var i = 0; i < response.errors.length; i++) {
                            ajaxHandler.alertError(localMessages.getErrorMessageByError(response.errors[i]));
                        }
                        return false;
                    }
                }
            }
        });
    },
    deleteFile: function () {
        var file_uploading_container = $('js-new_post_file_field'),
            uploaded_image_container = file_uploading_container.getElement('.b-post_edit_image'),
            media_id_input = $('js-new_post_media_id');
        file_uploading_container.getElement('.b-new_post_image_delete_file').addClass('hidden');
        if (media_id_input) {
            media_id_input.destroy();
        }
        if (uploaded_image_container) {
            uploaded_image_container.destroy();
        }
        $('js-file_uploader_drag').innerHTML = 'или перетащить сюда';

        newPostHandler.submitDraft();
    },
    toggleSubscribedList: function (event) {
        event.preventDefault();
        if (this.subscribedListContainer.hasClass('opened')) {
            this.subscribedListContainer.removeClass('opened');
            this.subscribed_list_control.innerHTML = 'еще';
        } else {
            this.subscribedListContainer.addClass('opened');
            this.subscribed_list_control.innerHTML = 'свернуть';
        }
    },
    setSubscribedDomainsListStyle: function () {
        if ($$('.b-new_post_domain_subscribed_list')[0]) {
            var containerHeight = 20;
            this.subscribedListContainer = $$('.b-new_post_domain_subscribed_list')[0];
            this.subscribedListContent = this.subscribedListContainer.getElement('.b-new_post_domain_subscribed_list_content');

            this.subscribed_list_control.toggleClass('hidden', this.subscribedListContent.getCoordinates().height <= containerHeight);

        }
    },
    showTagsContainer: function (target) {
        $('js-new_post_tags').focus();
    },
    serializeToJSON: function () {
        var fields = {
            type: 'post',
            title: $('js-new_post_header').value,
            content: $('js-new_post_body').value,
            url: $('js-new_post_url').value,
            image: null
        };
        var image_input = $('js-new_post_media_id');
        if (image_input && parseInt(image_input.value, 10) > 0) {
            fields.image = image_input.value.trim();
        }
        return fields;
    }
};
newSubsiteHandler = {
    validateNewSubsiteForm: function () {
        if ($('js-new_subdirty_prefix').value.trim().length < 1) {
            new futuAlert('Не следует вот так просто нажимать на эту кнопку! Придумайте хорошее имя домена третьего уровня!');
            ajaxHandler.highlightField($('js-new_subdirty_prefix'));
            $('js-new_subdirty_prefix').focus();
            return false;
        }
        return true;
    },
    sendNewSubsiteForm: function () {
        if (newSubsiteHandler.validateNewSubsiteForm()) {
            new futuAjax({
                button: $('js-new_subdirty_submit_button'),
                url: ajaxUrls.domain_create,
                data: $('js-new_subdirty_form').toQueryString(),
                color_to: 0.5,
                color_from: 1,
                attribute: 'opacity',
                onLoadFunction: function (response) {
                    var newSubsiteForm = $('js-subdomain-creation'),
                        createdContent = $('js-subdomain_created'),
                        newDomainUrl = response.domain.url,
                        now = new Date(response.domain.created * 1000),
                        domainsList = createdContent.getElements('.b-dirty_domains_list_item'),
                        subscriptions = response.official_subscriptions;

                    // Замена ссылок, даты создания, названия сайта
                    $('js-new_domain').innerHTML = newDomainUrl;
                    createdContent.getElement('.b-new_subdirty_name').href = 'http://' + newDomainUrl;
                    createdContent.getElement('.b-new_subdirty_post_link').href = '/create/post/on/' + newDomainUrl.split('.')[0];
                    createdContent.getElement('.b-new_subdirty_settings_link').href = 'http://' + newDomainUrl + '/controls';
                    createdContent.getElement('.b-new_subdirty_date').innerHTML = now.format('d mmmm yyyy') + ' года';

                    for (var i = 0, l = domainsList.length; i < l; i++) {
                        var url = domainsList[i].getElement('.b-dirty_domain_url'),
                            controlContainer = domainsList[i].getElement('.b-subsite_controls');
                        for (var j = 0, jl = subscriptions.length; j < jl; j++) {
                            if (url.innerHTML.trim() == subscriptions[j].url.trim()) {
                                var subscriptionButton = new Element('div', {
                                    html: '<a onclick="feedSettingsHandler.subscribe(this, \'domains\', \'{id}\', \'b-dirty_domains_list_item\'); return false;" class="b-fui_icon_button b-fui_icon_button_subscribe" href="#"><span></span><i>подписаться</i><em></em></a>\
											<a onclick="feedSettingsHandler.unsubscribe(this, \'domains\', \'{id}\', \'b-dirty_domains_list_item\'); return false;" class="b-fui_icon_button b-fui_icon_button_unsubscribe" href="#"><span></span><i class="js-subscribed_text">подписан</i><i class="js-unsubscribe_text">отписаться</i><em></em></a>'.substitute({
                                            id: subscriptions[j].id
                                        })
                                });

                                domainsList[i].getElement('.b-subsite_controls').innerHTML = subscriptionButton.innerHTML;
                                if (subscriptions[j].subscribed) {
                                    controlContainer.addClass('js-subscribed');
                                }
                            }
                        }
                    }

                    // Скролл к верху страницы
                    new Fx.Scroll(window, {
                        offset: {
                            x: 0,
                            y: 0
                        },
                        duration: 222
                    }).toTop();

                    // Отображение информации о создании нового сайта, удаление формы создания
                    createdContent
                        .removeClass('hidden')
                        .setStyle('opacity', 0);
                    newSubsiteForm.addClass('hidden');
                    /*new Fx.Tween(createdContent, {
                     duration: 400
                     })
                     .start('opacity', 1)
                     .addEvent('complete', function() {
                     newSubsiteForm.destroy();
                     });*/
                }
            });
        }
    },
    toggleUploader: function () {
        var logoContainer = $('js-subdirty_logo');
        if (logoContainer.hasClass('hidden')) {
            logoContainer.removeClass('hidden');
            if (!this.file_uploader) {
                newSubsiteHandler.initFileUploader();
            }
        } else {
            logoContainer.addClass('hidden');
        }
    },
    initFileUploader: function () {
        if (!utils.isFileUploadSupported()) {
            $('js-file_upload').addClass('hidden');
            return;
        }
        var dragContainer = $('js-file_uploader_subsite_logo_drag');

        this.file_uploader = new futuFileUploader({
            container: 'js-file_uploader_subsite_logo',
            browseButton: 'js-file_uploader_subsite_logo_button',
            dropElement: 'js-file_uploader_subsite_logo_drag',
            dropElementNode: dragContainer,
            uploadProgress: function (up, file) {
                dragContainer.innerHTML = file.name + '&nbsp;(' + file.percent + '%)';
            }.bind(this),
            uploadComplete: function (up, file, response) {
                ajaxHandler.highlightField($('js-file_uploader_subsite_logo'), Colors.background_color, Colors.links_color);
            }.bind(this),
            fileUploaded: function (up, file, response) {
                var response = JSON.decode(response.response);
                if (response.media_id) {
                    if ($('js-file_uploader_subsite_logo_file')) {
                        $('js-file_uploader_subsite_logo_file').value = response.media_id;
                    } else {
                        var subsite_logo_file_input = new Element('input', {
                            type: 'hidden',
                            value: response.media_id,
                            name: 'logo',
                            id: 'js-file_uploader_subsite_logo_file'
                        });
                        subsite_logo_file_input.inject($('js-file_uploader_subsite_logo'), 'after');
                    }
                } else {
                    if (response.status == 'ERR') {
                        for (var i = 0; i < response.errors.length; i++) {
                            ajaxHandler.alertError(localMessages.getErrorMessageByError(response.errors[i]));
                        }
                        return false;
                    }
                }
            }
        });
    }
};
invitesHandler = {
    resendInvite: function (button, invite_id) {
        new futuAjax({
            button: $(button),
            color_to: '#90BBED',
            color_from: '#556E8C',
            attribute: 'color',
            url: ajaxUrls.invite_resend,
            data: 'id=' + invite_id,
            onLoadFunction: function (response) {
                new futuAlert('Письмо отправлено заново.');
            }
        });
    },
    cancelInvite: function (button, invite_id) {
        new futuAjax({
            button: $(button),
            color_to: '#90BBED',
            color_from: '#556E8C',
            attribute: 'color',
            url: ajaxUrls.invite_cancel,
            data: 'id=' + invite_id,
            onLoadFunction: function (response) {
                $(button).getParent('.b-invite_issued').destroy();
                if (!$('js-issued_invites_holder').getElement('.b-invite_issued')) {
                    $('js-issued_invites_holder').innerHTML = 'У Вас нет ни одного выпущенного приглашения.';
                }
                new futuAlert('Приглашение отменено');
            }
        });
    },
    validateInviteForm: function () {
        if ($('js-new_invite_email').value.trim().length < 1) {
            new futuAlert('Email-адрес необходимо обязательно указать.');
            return false;
        }
        if ($('js-new_invite_password').value.trim().length < 1) {
            new futuAlert('Это одно из немногих мест, где необходим ваш пароль. Безопасность, сами понимаете.');
            return false;
        }
        if ($('recaptcha_response_field').value.trim().length < 1) {
            new futuAlert('Будьте добры, присмотритесь к картинке и напишите два подходящих слова.');
            return false;
        }
        return true;
    },
    sendInvite: function () {
        if (invitesHandler.validateInviteForm()) {
            new futuAjax({
                button: $('js-new_invite_submit'),
                color_to: '#cccccc',
                color_from: '#e9e9e9',
                url: ajaxUrls.invite_issue,
                data: $('js-new_invite_form').toQueryString(),
                onLoadFunction: function (response) {
                    var new_issued_invite_element = new Element('div', {'class': 'b-invite_issued'});
                    new_issued_invite_element.innerHTML = '<a href="mailto:{invite_email}" class="b-invite_issued_email">{invite_email}</a>\
						<div class="b-invite_issued_controls"><a href="#" onclick="invitesHandler.resendInvite(this, \'{invite_id}\'); return false;">отправить ещё раз?</a> | <a href="#" onclick="invitesHandler.cancelInvite(this, \'{invite_id}\'); return false;">забить?</a></div>'.substitute({
                            'invite_email': response.invite.email,
                            'invite_id': response.invite.id
                        });

                    if (!$('js-issued_invites_holder').getElement('.b-invite_issued')) {
                        $('js-issued_invites_holder').innerHTML = 'Вы уже отправили приглашения на следующие адреса, но они никак на это не отреагировали:';
                    }
                    new_issued_invite_element.inject($('js-issued_invites_holder'));

                    new futuAlert('Приглашение отправлено');

                    $('js-new_invite_form').reset();
                    Recaptcha.reload('t');
                },
                onCustomErrorFunction: function (response) {
                    if (response.status == 'ERR') {
                        var message = '';
                        for (var i = 0; i < response.errors.length; i++) {
                            message += localMessages.getErrorMessageByError(response.errors[i], ajaxUrls.invite_issue);
                            if (response.errors.length > 1 && i < response.errors.length - 1) {
                                message += '<br>';
                            }
                        }
                        ajaxHandler.alertError(message);
                        Recaptcha.reload('t');
                    }
                }
            });
        }
    }
};
registerHandler = {
    errors: {
        login: {
            valid: false,
            field: 'loginField'
        },
        password: {
            valid: false,
            field: 'passwordField'
        },
        confirmPassword: {
            valid: false,
            field: 'passwordConfirmField'
        },
        email: {
            valid: false,
            field: 'emailField'
        },
        gender: {
            valid: false
        },
        agreement: {
            valid: false
        },
        recaptcha: {
            valid: false,
            field: 'recaptchaField'
        }
    },
    connectedNetworks: [],
    socialData: null,
    sanitizedSocialData: null,

    init: function () {
        var socialNetworksButtons = $$('.b-social_network_icon');

        this.form = $$('.b-form');
        this.loginField = $('js-register_username');
        this.passwordField = $('js-register_password');
        this.passwordConfirmField = $('js-register_password_confirm');
        this.emailField = $('js-register_email');
        this.agreementInput = $('js-register_agreement');
        this.genderDefaultInput = $('js-register_sex_male');
        this.genderInputs = $$('.b-user_gender');
        this.recaptchaField = $('recaptcha_response_field');
        this.recaptcha = $('js-recaptcha_reloader');

        try {
            this.socialData = JSON.parse($('js-social-data').value);
        } catch (err) {
        }
        try {
            this.sanitizedSocialData = JSON.parse($('js-sanitized-social-data').value);
        } catch (err) {
        }

        this.form.addListener('submit', function (event) {
            event.preventDefault();
            this.sendRegisterForm();
        }.bind(this));

        this.recaptcha.addListener('mousedown', function (event) {
            Recaptcha.reload();
            if (this.validateRecaptchaTimeout) {
                clearTimeout(this.validateRecaptchaTimeout);
            }
        }.bind(this));
        this.recaptcha.addListener('click', function (event) {
            event.preventDefault();
        });

        this.loginField.addListener('keyup', this.removeFieldError.bind(this, this.loginField));
        this.loginField.addListener('blur', this.validateLogin.bind(this, true));
        this.passwordField.addListener('blur', this.validatePassword.bind(this));
        this.passwordField.addListener('keyup', this.removeFieldError.bind(this, this.passwordConfirmField));
        this.passwordField.addListener('keyup', this.validatePasswordStrength.bind(this));
        this.passwordConfirmField.addListener('blur', this.validatePasswordConfirm.bind(this));
        this.passwordConfirmField.addListener('keyup', function () {
            this.passwordConfirmFieldFocused = true;
            this.removeFieldError(this.passwordConfirmField);
        }.bind(this));
        this.emailField.addListener('keyup', this.removeFieldError.bind(this, this.emailField));
        this.emailField.addListener('blur', this.validateEmail.bind(this, true));
        this.agreementInput.addEvent('change', this.validateAgreement.bind(this));
        this.genderInputs.addEvent('change', this.validateGender.bind(this));
        this.recaptchaField.addListener('blur', this.validateRecaptcha.bind(this, true));
        this.recaptchaField.addListener('keyup', this.removeRecaptchaError.bind(this));

        socialNetworksButtons.addEvent('click', function (event) {
            event.preventDefault();
            var target = event.target,
                param = '',
                name,
                url,
                displayType;

            if (!target.hasClass('active')) {
                name = target.get('data-network');
                if (name == 'facebook') {
                    displayType = $(document.body).hasClass('l-touch_capable') ? 'touch' : 'popup';
                    param = '?display=' + displayType;
                }
                url = '/connect/' + name + '/login/' + param;
                socialNetworksHandler.showPopup(url, name);
            }
        });

        window.addEvent('social_auth_complete', function (event) {
            this.updateSocialNetworksData(event.detail);
        }.bind(this));

        // Заполнение полей формы из социальных сетей
        this.checkConnectedNetworks();
        this.setSocialNetworksData();
    },

    validateLogin: function (doValidationRequest, login, showError) {
        var container = this.loginField.getParent('.b-form_cell'),
            errorText = container.getElement('.b-form_error_content'),
            validSign = container.getElement('.b-input_validation'),
            error,
            validLogin,
            login = login ? login : this.loginField.value.trim();

        if (login.length < 1) {
            error = new Element('div', {
                'class': 'b-form_error',
                html: 'Вы не ввели имя пользователя.'
            });
            this.showError(this.loginField, error);
            this.showValidSign(this.loginField, 'invalid', 'valid');
            this.errors.login.valid = false;
        } else {
            this.errors.login.valid = true;
            if (doValidationRequest) {
                new futuAjax({
                    button: this.loginField,
                    color_to: '',
                    color_from: '',
                    url: ajaxUrls.validate_login,
                    data: 'username=' + encodeURIComponent(login),
                    checkResponseFunction: function (ajaxObj, text, url) {
                        return this.checkAjaxResponseFunction(ajaxObj, text, url, showError);
                    }.bind(this),
                    onLoadFunction: function (response) {
                        this.removeError(this.loginField);
                        this.showValidSign(this.loginField, 'valid', 'invalid');
                        this.errors.login.valid = true;
                        this.loginField.value = login;
                    }.bind(this)
                });
            }
        }
    },

    removeFieldError: function (input) {
        if (input) {
            this.removeError(input);
            this.hideValidSign(input);
        }
    },

    validatePassword: function () {
        var container = this.passwordField.getParent('.b-form_cell'),
            errorText = container.getElement('.b-form_error_content'),
            validSign = container.getElement('.b-input_validation'),
            strengthSign = container.getElement('.b-password_validation'),
            error;

        if (this.passwordField.value.trim().length < 1) {
            error = new Element('div', {
                'class': 'b-form_error',
                html: 'Заполните поле.'
            });
            this.showError(this.passwordField, error);
            strengthSign.setStyle('display', 'none');
            this.showValidSign(this.passwordField, 'invalid', 'valid');
            this.errors.password.valid = false;
        } else if (this.passwordField.value.trim().length < 8) {
            error = new Element('div', {
                'class': 'b-form_error',
                html: 'Короткий пароль!'
            });
            this.showError(this.passwordField, error);
            strengthSign.setStyle('display', 'none');
            this.showValidSign(this.passwordField, 'invalid', 'valid');
            this.errors.password.valid = false;
        } else {
            this.errors.password.valid = true;
        }
        this.validatePasswordConfirm();
    },

    // Валидация стойкости пароля
    validatePasswordStrength: function () {
        if (this.checkingStrengthTimeout) {
            clearTimeout(this.checkingStrengthTimeout);
        }
        this.checkingStrengthTimeout = setTimeout(function () {
            var container = this.passwordField.getParent('.b-form_cell'),
                errorText = container.getElement('.b-form_error_content'),
                validSign = container.getElement('.b-input_validation'),
                className = 'b-password_validation',
                errorTextClassName = 'b-form_error',
                strengthSign = container.getElement('.' + className),
                strengthValidation = typeof zxcvbn === 'function' ? zxcvbn(this.passwordField.value) : false,
                error;
            if (this.passwordField.value.trim().length < 1) {
                this.validatePassword();
            } else if (strengthValidation) {
                error = new Element('div');
                switch (strengthValidation.score) {
                    case 0:
                    case 1:
                        className += ' b-password_validation_invalid';
                        error.innerHTML = 'Небезопасный пароль!';
                        break;
                    case 2:
                    case 3:
                        className += ' b-password_validation_weak';
                        errorTextClassName = 'b-form_error_weak';
                        error.innerHTML = 'Слабый пароль.';
                        break;
                    case 4:
                        className += ' b-password_validation_valid';
                        errorTextClassName = 'b-form_error_valid';
                        error.innerHTML = '';
                        break;
                }
                validSign.setStyle('display', 'none');
                strengthSign.setStyle('display', 'block');
                strengthSign.setProperty('class', className);
                error.setProperty('class', errorTextClassName);
                this.showError(this.passwordField, error);
            } else {
                validSign.setStyle('display', 'none');
                strengthSign.setStyle('display', 'none');
                this.removeError(errorText);
            }
        }.bind(this), 50);
    },

    validatePasswordConfirm: function () {
        var container = this.passwordConfirmField.getParent('.b-form_cell'),
            errorText = container.getElement('.b-form_error_content'),
            validSign = container.getElement('.b-input_validation'),
            error;

        if (this.passwordConfirmFieldFocused) {
            if (this.passwordField.value != this.passwordConfirmField.value) {
                error = new Element('div', {
                    'class': 'b-form_error',
                    html: 'О нет! Они не совпададзе!'
                });
                this.showError(this.passwordConfirmField, error);
                this.showValidSign(this.passwordConfirmField, 'invalid', 'valid');
                this.errors.confirmPassword.valid = false;
            } else if (this.passwordConfirmField.value.trim().length > 0 && this.errors.password.valid) {
                this.removeError(errorText);
                this.showValidSign(this.passwordConfirmField, 'valid', 'invalid');
                this.errors.confirmPassword.valid = true;
            } else {
                this.errors.confirmPassword.valid = false;
            }
        }
    },
    validateEmail: function (doValidationRequest, email, showError) {
        var container = this.emailField.getParent('.b-form_cell'),
            errorText = container.getElement('.b-form_error_content'),
            validSign = container.getElement('.b-input_validation'),
            email = email ? email : this.emailField.value.trim(),
            error;

        if (email.length < 1) {
            error = new Element('div', {
                'class': 'b-form_error',
                html: 'Вы не указали адрес почты.'
            });
            this.showError(this.emailField, error);
            this.showValidSign(this.emailField, 'invalid', 'valid');
            this.errors.email.valid = false;
        } else {
            this.errors.email.valid = true;
            if (doValidationRequest) {
                new futuAjax({
                    button: this.emailField,
                    color_to: '',
                    color_from: '',
                    url: ajaxUrls.validate_email,
                    data: 'email=' + encodeURIComponent(email),
                    checkResponseFunction: function (ajaxObj, text, url) {
                        return this.checkAjaxResponseFunction(ajaxObj, text, url, showError);
                    }.bind(this),
                    onLoadFunction: function (response) {
                        this.removeError(errorText);
                        this.showValidSign(this.emailField, 'valid', 'invalid');
                        this.errors.email.valid = true;
                        this.emailField.value = email;
                    }.bind(this)
                });
            }
        }
    },
    validateGender: function () {
        var container = this.genderDefaultInput.getParent('.js-input_container'),
            errorText = container.getElement('.b-form_error_content'),
            genderInput = $$('.b-user_gender:checked'),
            error;

        if (!genderInput.length) {
            error = new Element('div', {
                'class': 'b-form_error',
                html: 'Вы не указали свой пол.'
            });
            this.showError(this.genderDefaultInput, error);
            this.errors.gender.valid = false;
        } else {
            this.errors.gender.valid = true;
            this.removeError(errorText);
        }
    },
    validateAgreement: function () {
        var container = this.agreementInput.getParent('.b-agreement'),
            errorText = container.getElement('.b-form_error_content'),
            error;

        if (!this.agreementInput || !this.agreementInput.get('checked')) {
            error = new Element('div', {
                'class': 'b-form_error',
                html: 'Необходимо принять условия пользовательского соглашения.'
            });
            this.showError(this.agreementInput, error);
            this.errors.agreement.valid = false;
        } else {
            this.removeError(errorText);
            this.errors.agreement.valid = true;
        }
    },
    validateRecaptcha: function (timeout) {
        var validateFunc = function () {
            var container = this.recaptchaField.getParent('.b-form_field'),
                errorText = container.getElement('.b-form_error_content'),
                error;
            if (Recaptcha.get_response().length == 0) {
                error = new Element('div', {
                    'class': 'b-form_error',
                    html: 'Присмотритесь к картинке и напишите два подходящих слова.'
                });
                this.showError(this.recaptchaField, error);
                this.errors.recaptcha.valid = false;
            } else {
                this.errors.recaptcha.valid = true;
            }
        }.bind(this);
        if (timeout) {
            this.validateRecaptchaTimeout = setTimeout(validateFunc, 100);
        } else {
            validateFunc();
        }
    },
    removeRecaptchaError: function () {
        var container = this.recaptchaField.getParent('.js-input_container');

        this.removeError(this.recaptchaField);

        if (this.validateRecaptchaTimeout) {
            clearTimeout(this.validateRecaptchaTimeout);
        }
    },
    // Отображение и анимация ошибки
    showError: function (field, error) {
        var container = field.getParent('.js-input_container'),
            errorContainer = container.getElement('.b-form_error_content');

        // Очищаем элемент-контейнет ошибки
        this.removeError(field);

        if (error) {
            var opacityAnimation = function () {
                new Fx.Morph(error, {
                    duration: 222
                }).start({
                        opacity: 1
                    });
            };
            // Вставляем элемент с ошибкой в контейнер
            errorContainer.adopt(error);

            // Если контейнер не содержал ранее в себе каких-либо ошибок,
            // происходит анимация высоты контейнера
            if (!errorContainer.hasClass('opened')) {
                error.setStyle('opacity', 0);
                errorContainer.addClass('opened');
                opacityAnimation();
            }
        }
    },
    removeError: function (input) {
        var container = input.getParent('.js-input_container'),
            errorContainer = container.getElement('.b-form_error_content');

        errorContainer.empty();
    },
    showValidSign: function (input, addClass, removeClass) {
        var container = input.getParent('.b-form_cell'),
            validSign = container.getElement('.b-input_validation');

        validSign
            .setStyle('display', 'block')
            .addClass('b-input_' + addClass)
            .removeClass('b-input_' + removeClass);
    },
    hideValidSign: function (input) {
        var container = input.getParent('.js-input_container'),
            validSign = container.getElement('.b-input_validation');

        validSign.setStyle('display', 'none');
    },
    getCities: function (city_id) {
        if (!$('js-register_country').value) {
            $('js-register_city_holder').innerHTML = '';
        } else {
            new futuAjax({
                button: $('js-register_country'),
                attribute: 'color',
                color_to: '',
                color_from: '',
                url: ajaxUrls.geo_cities,
                data: 'country=' + $('js-register_country').value,
                onLoadFunction: function (response) {
                    var iHTML = '<select name="city" id="js-register_city" class="i-form_select i-form_select__wide"><option value="">Город</option>'
                    $A(response.cities).each(function (city) {
                        iHTML += '<option value="{city_id}">{city_name}</option>'.substitute({
                            'city_id': city.id,
                            'city_name': city.name
                        });
                    });
                    iHTML += '</select>';
                    $('js-register_city_holder').innerHTML = iHTML;
                    if (city_id) {
                        $('js-register_city_holder').getElement('select').value = '' + city_id;
                    }
                }
            });
        }
    },
    setEmail: function (data) {
        if (data.email && this.emailField.value.trim() == '') {
            this.emailField.value = data.email;
        }
    },
    updateSocialNetworksData: function (data) {
        // Эти две строчки требуются для селениум-тестов на регистрацию,
        // не удаляйте их, пожалуйста
        window.snEvents = window.snEvents || [];
        window.snEvents.append([data]);

        if (data.status == 'already_exists') {
            new futuAlert('Вы можете <a href="#" id="js-login_link">войти</a> под аккаунтом <a href="/user/' + data.user.login + '">' + data.user.login + '</a> ', true);
            $('js-login_link').addEvent('click', function (event) {
                event.preventDefault();
                Cookie.write('uid', data.user.uid, {
                    domain: '.' + (globals.domain.url.split('//')[1])
                });
                Cookie.write('sid', data.user.sid, {
                    domain: '.' + (globals.domain.url.split('//')[1])
                });
                window.location.href = '/';
            });
        } else {
            Cookie.write('uid', data.user.uid, {
                domain: '.' + (globals.domain.url.split('//')[1])
            });
            Cookie.write('sid', data.user.sid, {
                domain: '.' + (globals.domain.url.split('//')[1])
            });

            // Если есть логин, значит пользователь уже создавал аккаунт,
            // но не активировал его, перекинем его на главную

            if (data.user.login && data.user.login.length > 0) {
                window.location.href = '/';
                return;
            }
            var addToArray = true;

            if (this.connectedNetworks.length > 0) {
                for (var i = 0; i < this.connectedNetworks.length; i++) {
                    if (this.connectedNetworks[i] == socialNetworksHandler.currentPopupName) {
                        addToArray = false;
                    }
                }
            }
            if (addToArray) {
                this.connectedNetworks.push(socialNetworksHandler.currentPopupName);
            }

            this.socialData = data.social_data;
            this.sanitizedSocialData = data.sanitized_social_data;

            this.checkConnectedNetworks();
            this.setSocialNetworksData();
        }
    },

    // Заполнение полей формы из социальных сетей
    setSocialNetworksData: function () {
        // Если пользователь еще не ввел логин, емейл, не указал пол, а из соц сетей пришли эти данные — подставим их в форму
        if (this.sanitizedSocialData) {
            if (this.sanitizedSocialData.username && this.sanitizedSocialData.username[0] && this.sanitizedSocialData.username[0].trim().length != '' && this.loginField.value.trim() == '') {
                this.validateLogin(true, this.sanitizedSocialData.username[0].trim(), false);
            }
            if (this.sanitizedSocialData.email && this.sanitizedSocialData.email[0] && this.sanitizedSocialData.email[0].trim().length != '' && this.emailField.value.trim() == '') {
                this.validateEmail(true, this.sanitizedSocialData.email[0].trim());
                this.emailField.value = this.sanitizedSocialData.email[0].trim();
            }
            if (this.sanitizedSocialData.gender && this.sanitizedSocialData.gender[0]) {
                $$('.b-user_gender[value="' + this.sanitizedSocialData.gender[0] + '"]').set('checked', true);
                this.validateGender();
            }
        }
    },
    // Проставление активного класса иконкам соцсетей, которые пользователь уже привязал
    checkConnectedNetworks: function () {
        var networksNames = [],
            networksNamesStr,
            msg;
        if (this.socialData) {
            for (var i in this.socialData) {
                if (this.socialData.hasOwnProperty(i)) {
                    $$('.b-social_network_icon-' + i).addClass('active');
                    networksNames.push(socialNetworksHandler.socialNetworksNames[i]);
                }
            }
        }
        if (networksNames.length > 0) {
            networksNamesStr = '';
            for (var i = 0, l = networksNames.length; i < l; i++) {
                if (i == l - 1 && l > 1) {
                    networksNamesStr += ' и ';
                } else if (i != 0) {
                    networksNamesStr += ', ';
                }
                networksNamesStr += networksNames[i];
            }

            if (networksNames.length == 1 || this.connectedNetworks.length == networksNames.length) {
                if (this.connectedNetworks.length == networksNames.length) {
                    networksNamesStr = socialNetworksHandler.socialNetworksNames[socialNetworksHandler.currentPopupName];
                }
                msg = 'Ваш будущий аккаунт будет связан с вашим аккаунтом в ' + networksNamesStr + '!';
            } else {
                msg = 'Ваш будущий аккаунт будет связан с вашими аккаунтами в ' + networksNamesStr + '!';
            }
            new futuAlert(msg);
        }
    },

    validateRegisterForm: function () {
        this.passwordConfirmFieldFocused = true;
        this.validateLogin();
        this.validatePassword();
        this.validatePasswordConfirm();
        this.validateEmail();
        this.validateGender();
        this.validateAgreement();
        this.validateRecaptcha();
        for (var i in this.errors) {
            if (this.errors.hasOwnProperty(i)) {
                if (!this.errors[i].valid) {
                    if (this.errors[i].field) {
                        this[this.errors[i].field].focus();
                    }
                    return false;
                }
            }
        }
        return true;
    },
    sendRegisterForm: function () {
        if (registerHandler.validateRegisterForm()) {
            var data = $('js-register_form').toQueryString();

            new futuAjax({
                button: $('js-register_submit'),
                color_to: '0.5',
                color_from: '1',
                attribute: 'opacity',
                url: ajaxUrls.register,
                data: data,
                checkResponseFunction: function (ajaxObj, text, url) {
                    return this.checkAjaxResponseFunction(ajaxObj, text, url);
                }.bind(this),
                onLoadFunction: function (response) {
                    ga('send', 'event', 'users', 'register', {'page': '/register'});
                    window.location.href = '/';
                },
                onCustomErrorFunction: function () {
                    Recaptcha.reload('t');
                    for (var i in this.errors) {
                        if (this.errors.hasOwnProperty(i)) {
                            if (!this.errors[i].valid) {
                                if (this.errors[i].field) {
                                    this[this.errors[i].field].focus();
                                }
                                return false;
                            }
                        }
                    }
                }.bind(this)
            });
        }
    },
    checkAjaxResponseFunction: function (ajaxObj, text, url, showError) {
        var showError = typeof showError !== 'undefined' ? showError : true;
        if (text) {
            var response = JSON.decode(ajaxObj);
        } else {
            var response = JSON.decode(ajaxObj.responseText);
        }

        if (response.status == 'ERR') {
            for (var i = 0; i < response.errors.length; i++) {
                if (this.validationErrors[response.errors[i].code] && showError) {
                    this.validationErrors[response.errors[i].code].call(this, response.errors[i], url);
                }
            }
            return false;
        }

        return response;
    },
    validationErrors: {
        username_in_use: function (error, url) {
            var loginsLength = error.alternative_logins.length,
                i = Math.floor(Math.random() * (loginsLength - 1)),
                text = 'Имя &ldquo;' + this.loginField.value.trim() + '&rdquo; уже занято, давайте <a href="#" class="js-valid_login">' + error.alternative_logins[i] + '</a>?',
                error = this.createErrorElement(text),
                validLogin = error.getElement('.js-valid_login');

            // Навешивание обработчика клика.
            // Замена введенного имени пользователя на предложенное имя
            validLogin.addListener('mousedown', function (event) {
                this.loginField.value = validLogin.innerHTML.trim();
                this.validateLogin(true, validLogin.innerHTML.trim());
                this.errors.login.valid = true;
            }.bind(this));
            validLogin.addListener('click', function (event) {
                event.preventDefault();
            }.bind(this));

            this.errors.login.valid = false;

            this.showError(this.loginField, error);
            this.showValidSign(this.loginField, 'invalid', 'valid');
        },
        invalid_username: function (error, url) {
            var text = localMessages.getErrorMessageByError(error, url),
                error = this.createErrorElement(text);

            this.errors.login.valid = false;
            this.showError(this.loginField, error);
            this.showValidSign(this.loginField, 'invalid', 'valid');
        },
        invalid_email: function (error, url) {
            var text = localMessages.getErrorMessageByError(error, url),
                error = this.createErrorElement(text);

            this.errors.email.valid = false;
            this.showError(this.emailField, error);
            this.showValidSign(this.emailField, 'invalid', 'valid');
        },
        email_in_use: function (error, url) {
            var text = localMessages.getErrorMessageByError(error, url),
                error = this.createErrorElement(text);

            this.errors.email.valid = false;
            this.showError(this.emailField, error);
            this.showValidSign(this.emailField, 'invalid', 'valid');
        },
        invalid_captcha_response: function (error, url) {
            var text = localMessages.getErrorMessageByError(error, url),
                error = this.createErrorElement(text);

            // Перезагружаем рекапчу без триггера фокуса на инпуте, чтобы осталось видно сообщение об ошибке
            Recaptcha.reload('t');
            this.errors.recaptcha.valid = false;
            this.showError(this.recaptchaField, error);
        },
        invalid_gender: function (error, url) {
            var text = localMessages.getErrorMessageByError(error, url),
                error = this.createErrorElement(text);

            this.errors.gender.valid = false;
            this.showError(this.genderDefaultInput, error);
        }
    },
    createErrorElement: function (text) {
        var error = new Element('div', {
            'class': 'b-form_error',
            html: text
        });
        return error;
    }
};

usersHandler = {
    searchUsers: function () {
        var data = $('js-users_search_form').toQueryString();
        new futuAjax({
            button: $('js-users_search_form').getElement('.b-fui_button'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.search_users,
            data: data,
            onLoadFunction: function (response) {
            }
        });
    }
};
searchHandler = {
    init: function () {
        window.addEvent('resize', function () {
            this.shrinkHeaderSearch(0);
        }.bind(this));
    },
    submitQuery: function (search_form, domain) {
        if (search_form.getParent('.b-header_search')) {
            var input = $('js-header_search_input');
            var input_holder = input.getParent('.b-header_search_input_holder');
            var search_holder = input.getParent('.b-header_search');
//			console.log(input.getCoordinates().width);
            if (search_holder.hasClass('b-header_search_input_shrinked')) {
                search_holder.removeClass('b-header_search_input_shrinked');
                input_holder.get('morph').removeEvents('complete');
                input_holder.set('morph', {duration: 333, onComplete: function () {
                    input.focus();
                    (function () {
                        document.addEvent('click', function () {
                            searchHandler.shrinkHeaderSearch();
                        });
                    }).delay(200);
                }});
                input_holder.morph({
                    width: input.getCoordinates().width
                });
            } else {
                var query = $(search_form).getElement('input[type="text"]').value;
                if (domain) {
                    window.location.href = 'http://' + domain + '/search/' + encodeURIComponent(query);
                } else {
                    window.location.href = '/search/' + encodeURIComponent(query);
                }
            }
        } else {
            var query = $(search_form).getElement('input[type="text"]').value;
            if (domain) {
                window.location.href = 'http://' + domain + '/search/' + encodeURIComponent(query);
            } else {
                window.location.href = '/search/' + encodeURIComponent(query);
            }
        }
    },
    shrinkHeaderSearch: function (duration) {
        var duration = duration !== undefined ? duration : 333;
        var input_holder = $('js-header_search_input').getParent('.b-header_search_input_holder');
        var search_holder = $('js-header_search_input').getParent('.b-header_search');
        input_holder.get('morph').removeEvents('complete');
        input_holder.set('morph', {duration: duration, onComplete: function () {
            search_holder.addClass('b-header_search_input_shrinked');
        }});
        input_holder.morph({
            width: 0
        });
        document.removeEvent('click', searchHandler.shrinkHeaderSearch);
    }
};
rulesHandler = {
    toggleRuleExplanation: function (button) {
        var rule_element = $(button).getParent('.b-rules_list_item');
        var rule_explanation_element = rule_element.getElement('.b-rule_explanation');

        rule_explanation_element.get('morph').removeEvents('complete');
        rule_explanation_element.set('morph', {
            duration: 300,
            onComplete: function () {
                if (rule_explanation_element.hasClass('js-opened')) {
                    rule_explanation_element.set('styles', {
                        'maxHeight': 'none'
                    });
                }
            }
        });

        if (rule_explanation_element.hasClass('js-opened')) {
            rule_explanation_element.removeClass('js-opened');
            rule_explanation_element.set('styles', {
                'maxHeight': rule_explanation_element.offsetHeight
            });
            rule_explanation_element.morph({
                'maxHeight': 0
            });
        } else {
            rule_explanation_element.addClass('js-opened');
            rule_explanation_element.morph({
                'maxHeight': 200
            });
        }
    }
};
electionsHandler = {
    timer_caption_tooltip: null,
    setTimer: function (election_state, nomination_starts_at, nomination_ends_at, vote_ends_at, rule_ends_at) {
        var election_timer_element = $('js-elections_timer');
        var election_timer_till_caption = null;
        var current_date = (new Date()).getTime();
        var till_nomination_starts = nomination_starts_at * 1000 - current_date;
        var till_nomination_ends = nomination_ends_at * 1000 - current_date;
        var till_vote_ends = vote_ends_at * 1000 - current_date;
        var timer = 0;
        if (election_state == 'is_in_vote_phase') {
            timer = till_vote_ends;
            election_timer_till_caption = 'Выборы ' + (timer <= 0 ? 'планировали' : 'планируют') + ' закончиться ' + datesHandler.fancyDateFormat(vote_ends_at * 1000);
        } else if (election_state == 'is_in_nomination_phase') {
            timer = till_nomination_ends;
            election_timer_till_caption = 'Номинация ' + (timer <= 0 ? 'планировала' : 'планирует') + ' закончиться ' + datesHandler.fancyDateFormat(nomination_ends_at * 1000);
        } else if (election_state == 'is_just_created') {
            timer = till_nomination_starts;
            election_timer_till_caption = 'Номинация ' + (timer <= 0 ? 'планировала' : 'планирует') + ' начаться ' + datesHandler.fancyDateFormat(nomination_starts_at * 1000);
        }
        if (timer < 0) {
            timer = 0;
        }
        if (timer >= 0) {
            var x = timer / 1000;
            var timer_seconds = Math.floor(x % 60);
            if (timer_seconds < 10) {
                timer_seconds = '0' + timer_seconds;
            }
            x /= 60;
            var timer_minutes = Math.floor(x % 60);
            if (timer_minutes < 10) {
                timer_minutes = '0' + timer_minutes;
            }
            x /= 60;
            var timer_hours = Math.floor(x);
            $('js-elections_timer').innerHTML = [timer_hours, timer_minutes, timer_seconds].join(':');
            if (timer > 0) {
                electionsHandler.setTimer.delay(1000, this, arguments);
            }
        }
        if (election_timer_till_caption && !electionsHandler.timer_caption_tooltip) {
            election_timer_element.setAttribute('title', election_timer_till_caption);
            electionsHandler.timer_caption_tooltip = new Tips(election_timer_element, {
                showDelay: 0,
                hideDelay: 0,
                offset: {
                    x: function (tip, hovered) {
                        var tip_coords = tip.getCoordinates();
                        var hovered_coords = hovered.getCoordinates();
                        return -tip_coords.width + hovered_coords.width + 10
                    },
                    y: function (tip, hovered) {
                        var hovered_coords = hovered.getCoordinates();
                        return hovered_coords.height + 3
                    }
                },
                windowPadding: {
                    x: -100,
                    y: -100
                },
                fixed: true
            });
        } else {
            election_timer_element.store('tip:title', election_timer_till_caption);
        }

    },
    startNomination: function () {
        var domain_host = globals.domain.url.substr(7);
        var election_element = $('js-elections');
        var nomination_element = $('js-elections_nomination');
        election_element.removeClass('b-elections__nomination_not_started');
        election_element.addClass('b-elections__nomination');
        $('js-elections_status').innerHTML = 'Номинация на пост президента ' + domain_host;
    },
    completeNomination: function () {
        var domain_host = globals.domain.url.substr(7);
        var election_element = $('js-elections');
        var nomination_element = $('js-elections_nomination');
        election_element.removeClass('b-elections__nomination');
        $('js-elections_status').innerHTML = 'Выборы президента ' + domain_host;
        if (nomination_element) {
            if (nomination_element.hasClass('js-nominated')) {
                $('js-candidate_votes_count').removeClass('hidden');
                nomination_element.getElements('.b-elections_nomination_toggle, .b-elections_nomination_program').addClass('hidden');
            } else {
                nomination_element.addClass('hidden');
            }
        }
    },
    completeVoting: function () {
        var domain_host = globals.domain.url.substr(7);
        var election_element = $('js-elections');
        var nomination_element = $('js-elections_nomination');
        election_element.addClass('b-elections__nomination');
        $('js-elections_status').innerHTML = 'Выборы президента ' + domain_host + ' завершены.';
    },
    toggleVotingBooth: function () {
        var elections_element = $('js-elections');
        if (elections_element.hasClass('b-elections_block_opened')) {
            electionsHandler.hideVotingBooth();
        } else {
            electionsHandler.showVotingBooth();
        }
    },
    showVotingBooth: function () {
        var elections_element = $('js-elections');
        var elections_inner_element = $('js-elections_inner');
        if (!elections_element.hasClass('b-elections__nomination_not_started')) {
            electionsHandler.loadCandidates(function () {
                var elections_inner_element_height = elections_inner_element.getElement('div').getCoordinates().height;
                elections_element.addClass('b-elections_block_opened');

                elections_inner_element.get('morph').removeEvents('complete');
                elections_inner_element.set('morph', {
                    duration: 333,
                    onComplete: function () {
                        elections_inner_element.set('styles', {
                            'height': 'auto'
                        });
                    }
                });

                elections_inner_element.morph({
                    'height': elections_inner_element_height,
                    'top': 0
                });
            });
        }
    },
    hideVotingBooth: function () {
        var elections_element = $('js-elections');
        var elections_inner_element = $('js-elections_inner');
        var elections_inner_element_height = elections_inner_element.getCoordinates().height;

        elections_element.removeClass('b-elections_block_opened');

        elections_inner_element.set('styles', {
            'height': elections_inner_element_height
        });

        elections_inner_element.get('morph').removeEvents('complete');
        elections_inner_element.set('morph', {
            duration: 333
        });

        elections_inner_element.morph({
            'height': 0,
            'top': -10
        });
    },
    toggleNomination: function () {
        var nomination_element = $('js-elections_nomination');
        if (nomination_element.hasClass('js-nominated')) {
            new futuDialogPopup({
                text: 'Вы уверены, что хотите снять свою кандидатуру с выборов?',
                type: 'confirm',
                callback: function () {
                    nomination_element.removeClass('b-elections_nomination_opened');
                    electionsHandler.withdrawNomination();
                }
            });
        } else {
            electionsHandler.toggleProgram();
            nomination_element.getElement('.js-vote_controls').toggleClass('js-voted');
        }
    },
    toggleProgram: function () {
        var nomination_element = $('js-elections_nomination');
        nomination_element.toggleClass('b-elections_nomination_opened');
    },
    nominate: function () {
        var nomination_element = $('js-elections_nomination');
        var data = 'program=' + encodeURIComponent($('js-elections_nomination_program_body').value);
        new futuAjax({
            button: nomination_element.getElement('.b-elections_nomination_save input'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.elections_nominate,
            data: data,
            onLoadFunction: function (response) {
                var domain_host = globals.domain.url.substr(7);

                if (!nomination_element.hasClass('js-nominated')) {
                    new futuAlert('Поздравляем! Вы стали кандидатом в президенты ' + domain_host + '.');
                }

                nomination_element.addClass('js-nominated');
                nomination_element.removeClass('b-elections_nomination_opened');

                nomination_element.getElement('.b-elections_nomination_status').innerHTML = 'Вы баллотируетесь в президенты ' + domain_host + '. <span class="hidden" id="js-candidate_votes_count"></span>';

                electionsHandler.loadCandidates();
            }
        });
    },
    withdrawNomination: function () {
        var nomination_element = $('js-elections_nomination');
        new futuAjax({
            button: nomination_element.getElement('.b-fui_icon_button_unvote'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.elections_withdraw_nomination,
            data: '',
            onLoadFunction: function (response) {
                var domain_host = globals.domain.url.substr(7);

                new futuAlert('Вы больше не кандидат в президенты ' + domain_host + '.');

                nomination_element.removeClass('js-nominated');
                nomination_element.removeClass('b-elections_nomination_opened');
                nomination_element.getElement('.js-vote_controls').removeClass('js-voted');

                nomination_element.getElement('.b-elections_nomination_status').innerHTML = 'Вы можете баллотироваться в президенты ' + domain_host + '. <span class="hidden" id="js-candidate_votes_count"></span>';

                electionsHandler.loadCandidates();
            }
        });
    },
    vote: function (user_id, user_login, event) {

        var candidate_element = $('js-election_candidate_' + user_id);
        new futuAjax({
            button: candidate_element.getElement('.b-fui_icon_button_vote'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.elections_vote,
            data: 'candidate=' + encodeURIComponent(user_login),
            onLoadFunction: function (response) {
                var current_candidate_element = $('js-elections_candidates').getElement('.js-voted');
                if (current_candidate_element) {
                    current_candidate_element.removeClass('js-voted');
                    var current_candidate_votes_count = parseInt(current_candidate_element.getElement('.b-elections_candidate_votes_counter').innerHTML);
                    current_candidate_votes_count = current_candidate_votes_count ? current_candidate_votes_count - 1 : 0;
                    current_candidate_element.getElement('.b-elections_candidate_votes_counter').innerHTML = current_candidate_votes_count + ' ' + utils.getPlural(current_candidate_votes_count, ['голос', 'голоса', 'голосов']);

                    var current_candidate_id = current_candidate_element.getAttribute('data-user_id');
                    if (current_candidate_id == globals.user.id) {
                        $('js-candidate_votes_count').innerHTML = 'За вас отдали ' + current_candidate_votes_count + ' ' + utils.getPlural(current_candidate_votes_count, ['голос', 'голоса', 'голосов']);
                    }
                }

                candidate_element.addClass('js-voted');
                var candidate_votes_count = parseInt(candidate_element.getElement('.b-elections_candidate_votes_counter').innerHTML);
                candidate_votes_count = candidate_votes_count ? candidate_votes_count + 1 : 1;
                candidate_element.getElement('.b-elections_candidate_votes_counter').innerHTML = candidate_votes_count + ' ' + utils.getPlural(candidate_votes_count, ['голос', 'голоса', 'голосов']);

                electionsHandler.updateVotedForCandidate();
            }
        });
    },
    unvote: function (user_id, user_login) {
        var candidate_element = $('js-election_candidate_' + user_id);
        new futuAjax({
            button: candidate_element.getElement('.b-fui_icon_button_unvote'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.elections_unvote,
            data: 'candidate=' + encodeURIComponent(user_login),
            onLoadFunction: function (response) {
                candidate_element.removeClass('js-voted');
                var candidate_votes_count = parseInt(candidate_element.getElement('.b-elections_candidate_votes_counter').innerHTML);
                candidate_votes_count = candidate_votes_count ? candidate_votes_count - 1 : 0;
                candidate_element.getElement('.b-elections_candidate_votes_counter').innerHTML = candidate_votes_count + ' ' + utils.getPlural(candidate_votes_count, ['голос', 'голоса', 'голосов']);

                electionsHandler.updateVotedForCandidate();
            }
        });
    },
    updateVotedForCandidate: function () {
        if (!$('js-elections_candidates').hasClass('js-voting_requirements_not_met')) {
            var voted_for_candidate_element = $('js-elections_candidates').getElement('.js-voted');
            if (voted_for_candidate_element) {
                var user_login = voted_for_candidate_element.getElement('.b-elections_candidate_login').innerHTML;
                var user_id = voted_for_candidate_element.getAttribute('data-user_id');
                $('js-elections_candidates').getElement('.b-elections_chosen_candidate').innerHTML = 'Ваш кандидат&nbsp;&mdash; <a href="{user_profile_url}">{user_login}</a>'.substitute({
                    'user_profile_url': globals.base_domain.url + '/user/' + user_login,
                    'user_login': user_login
                });
            } else {
                if ($('js-elections_candidates').getElement('.b-elections_candidate')) {
                    $('js-elections_candidates').getElement('.b-elections_chosen_candidate').innerHTML = 'Кандидаты в президенты ' + globals.domain.url.substr(7) + ':';
                } else {
                    $('js-elections_candidates').getElement('.b-elections_chosen_candidate').innerHTML = 'Кандидатов пока нет.';

                }
            }
            var me_as_candidate_element = globals.user ? $('js-elections_candidates').getElement('.b-elections_candidate[data-user_id="' + globals.user.id + '"]') : null;
            if ($('js-candidate_votes_count') && me_as_candidate_element) {
                var my_votes_counter = me_as_candidate_element.getElement('.b-elections_candidate_votes_counter').innerHTML.trim();
                if (my_votes_counter.length == 0) {
                    my_votes_counter = '0 голосов';
                }
                $('js-candidate_votes_count').innerHTML = 'За вас отдали ' + my_votes_counter + '.';
            }
        }
    },
    loadCandidates: function (onload) {
        var candidates_element = $('js-elections_candidates');
        new futuAjax({
            button: candidates_element,
            attribute: 'opacity',
            color_to: 1,
            color_from: 1,
            url: ajaxUrls.elections_candidates,
            data: '',
            onLoadFunction: function (response) {
                if (!candidates_element.hasClass('js-voting_requirements_not_met')) {
                    if (response.candidates !== null && response.candidates.length > 0) {
                        candidates_element.getElement('.b-elections_chosen_candidate').innerHTML = 'Кандидаты в президенты ' + globals.domain.url.substr(7) + ':';
                    } else {
                        candidates_element.getElement('.b-elections_chosen_candidate').innerHTML = 'Кандидатов пока нет.';
                    }
                }
                candidates_element.getElement('.b-elections_candidates_list').innerHTML = response.template;
                candidates_element.removeClass('hidden');
                electionsHandler.initCandidatesPagination();
                electionsHandler.initCandidatesInfo();
                electionsHandler.updateVotedForCandidate();
                if (onload) {
                    onload();
                }
            }
        });
    },
    initCandidatesPagination: function () {
        var candidates_pages_elements = $('js-elections').getElements('.b-elections_candidates_list_page');
        var previous_page_button_element = $('js-elections_candidates').getElement('.b-arrow__prev');
        var next_page_button_element = $('js-elections_candidates').getElement('.b-arrow__next');

        $('js-elections_candidates_pagination').getParent('.b-pagination').addClass('hidden');
        $('js-elections_candidates_pagination').innerHTML = '';

        previous_page_button_element.addClass('hidden');
        next_page_button_element.addClass('hidden');

        if (candidates_pages_elements.length > 1) {
            $('js-elections_candidates_pagination').getParent('.b-pagination').removeClass('hidden');

            var pagination_html = '';
            for (var i = 0; i < candidates_pages_elements.length; i++) {
                pagination_html += '<a href="#" class="b-pagination-item {page_active}" data-page="{page_index}"> </a>'.substitute({
                    page_active: i == 0 ? 'active' : '',
                    page_index: i
                });
            }
            $('js-elections_candidates_pagination').innerHTML = pagination_html;

            var pagination_items = $('js-elections_candidates_pagination').getElements('.b-pagination-item');
            $A(pagination_items).each(function (pagination_item_element) {
                pagination_item_element.addEvent('click', function (e) {
                    e = new Event(e);
                    e.preventDefault();
                    $('js-elections_candidates_pagination').getElements('.b-pagination-item').removeClass('active');
                    pagination_item_element.addClass('active');
                    candidates_pages_elements.addClass('hidden');
                    var active_page_index = pagination_item_element.getAttribute('data-page');
                    candidates_pages_elements[active_page_index].removeClass('hidden');
                    if (active_page_index == 0) {
                        previous_page_button_element.addClass('hidden');
                    } else {
                        previous_page_button_element.removeClass('hidden');
                    }
                    if (active_page_index == candidates_pages_elements.length - 1) {
                        next_page_button_element.addClass('hidden');
                    } else {
                        next_page_button_element.removeClass('hidden');
                    }
                });
            });

            previous_page_button_element.addClass('hidden');
            next_page_button_element.removeClass('hidden');

            previous_page_button_element.removeEvents('click');
            next_page_button_element.removeEvents('click');

            previous_page_button_element.addEvent('click', function (e) {
                e = new Event(e);
                e.preventDefault();
                var active_page_index = $('js-elections_candidates_pagination').getElement('.active').getAttribute('data-page');
                if (active_page_index <= 1) {
                    previous_page_button_element.addClass('hidden');
                }
                $('js-elections_candidates_pagination').getElement('.active').removeClass('active');
                $('js-elections_candidates_pagination').getElement('.b-pagination-item[data-page="' + (active_page_index - 1) + '"]').addClass('active');
                next_page_button_element.removeClass('hidden');
                candidates_pages_elements.addClass('hidden');
                candidates_pages_elements[active_page_index - 1].removeClass('hidden');
            });

            next_page_button_element.addEvent('click', function (e) {
                e = new Event(e);
                e.preventDefault();
                var active_page_index = $('js-elections_candidates_pagination').getElement('.active').getAttribute('data-page');
                if (active_page_index >= candidates_pages_elements.length - 2) {
                    next_page_button_element.addClass('hidden');
                }
                $('js-elections_candidates_pagination').getElement('.active').removeClass('active');
                $('js-elections_candidates_pagination').getElement('.b-pagination-item[data-page="' + (1 + parseInt(active_page_index)) + '"]').addClass('active');
                previous_page_button_element.removeClass('hidden');
                candidates_pages_elements.addClass('hidden');
                candidates_pages_elements[1 + parseInt(active_page_index)].removeClass('hidden');
            });
        }
    },
    initCandidatesInfo: function () {
        var candidates_elements = $('js-elections').getElements('.b-elections_candidate');
        $A(candidates_elements).each(function (candidate_element) {
            var info_element_timeout = null;
            var candidate_login_element = candidate_element.getElement('.b-elections_candidate_login');
            var candidate_info_element = candidate_element.getElement('.b-elections_candidate_info');
            candidate_login_element.addEvent('mouseover', function () {
                $$('.b-elections_candidate_info').addClass('pos_hidden');
                $clear(info_element_timeout);
                info_element_timeout = window.setTimeout(function () {
                    electionsHandler.showCandidateInfo(candidate_login_element, candidate_info_element);
                }, 300);
            });
            candidate_element.addEvent('mouseout', function () {
                $clear(info_element_timeout);
                info_element_timeout = window.setTimeout(function () {
                    candidate_info_element.addClass('pos_hidden');
                }, 300);
            });
            candidate_info_element.addEvent('mouseout', function () {
                $clear(info_element_timeout);
                info_element_timeout = window.setTimeout(function () {
                    candidate_info_element.addClass('pos_hidden');
                }, 300);
            });
            candidate_info_element.addEvent('mouseover', function () {
                $clear(info_element_timeout);
            });
        });
    },
    showCandidateInfo: function (candidate_login_element, candidate_info_element) {
        var candidate_login_element_coordinates = candidate_login_element.getCoordinates();
        candidate_info_element.inject(document.body);

        candidate_info_element.removeClass('b-elections_candidate_info__left');
        candidate_info_element.removeClass('b-elections_candidate_info__bottom');

        var candidate_info_element_left = candidate_login_element_coordinates.left + candidate_login_element_coordinates.width + 20;
        var candidate_info_element_top = candidate_login_element_coordinates.top + 7;

        if (candidate_info_element_left + candidate_info_element.getCoordinates().width > $(window).getSize().x) {
            candidate_info_element_left = candidate_login_element_coordinates.left - candidate_info_element.getCoordinates().width - 20;
            candidate_info_element.addClass('b-elections_candidate_info__left');
        }
        if (candidate_info_element_left < 0) {
            candidate_info_element_left = 0;
            candidate_info_element_top = candidate_login_element_coordinates.top + 27
            candidate_info_element.addClass('b-elections_candidate_info__bottom');
        }

        candidate_info_element.set('styles', {
            'top': candidate_info_element_top,
            'left': candidate_info_element_left,
        });

        candidate_info_element.removeClass('pos_hidden');
    },
    impeach: function () {
        var impeachment_element = $('js-blog_info_government_impeachment');
        var impeach_button_element = impeachment_element.getElement('.b-fui_icon_button_impeach');
        new futuAjax({
            button: impeach_button_element,
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.elections_impeach,
            data: '',
            onLoadFunction: function (response) {
                impeachment_element.addClass('js-impeached');
                impeachment_element.getElement('.b-blog_info_government_president_impeachment_current').innerHTML = response.expelling_votes_count + ' ' + utils.getPlural(response.expelling_votes_count, ['голос', 'голоса', 'голосов']);
            }
        });
    },
    unimpeach: function () {
        var impeachment_element = $('js-blog_info_government_impeachment');
        var unimpeach_button_element = impeachment_element.getElement('.b-fui_icon_button_unimpeach');
        new futuAjax({
            button: unimpeach_button_element,
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.elections_unimpeach,
            data: '',
            onLoadFunction: function (response) {
                impeachment_element.removeClass('js-impeached');
                impeachment_element.getElement('.b-blog_info_government_president_impeachment_current').innerHTML = response.expelling_votes_count + ' ' + utils.getPlural(response.expelling_votes_count, ['голос', 'голоса', 'голосов']);
            }
        });
    }
};

var electionsInfoHandler = {
    currentIndex: 0,
    votedList: [],
    limit: 5,

    votingInfoInit: function () {
        this.infoContainer = $$('.js-voting_info')[0];
        this.lastVotedContainer = $('js-voted_users_list');

        this.loadVotes();
        this.firstLoad = true;

        this.votesRequestInterval = setInterval(function () {
            if (!this.lastVotedContainer.hasClass('hidden')) {
                this.loadVotes();
            }
        }.bind(this), 30000);

        this.votesRenderInterval = setInterval(function () {
            if (!this.lastVotedContainer.hasClass('hidden')) {
                this.renderVotes();
            }
        }.bind(this), 2000);

        if (this.lastVotedContainer) {
            this.lastVotedListInit();
        }
    },

    lastVotedListInit: function () {
        var closeBtn = $('js-cec_close'),
            state = Cookie.read('voted_users_state'),
            blockTopPanel = closeBtn.getParent('.b-cec_info');

        if (state == 'hidden') {
            this.lastVotedContainer.addClass('hidden');
            blockTopPanel.removeClass('opened');
        } else {
            this.lastVotedContainer.removeClass('hidden');
            blockTopPanel.addClass('opened');
        }

        closeBtn.addEvent('click', this.toggleVotedList.bind(this));
    },

    toggleVotedList: function (event) {
        event.preventDefault();
        var container = $('js-voted_users_list'),
            content = container.getElement('.js-voting_info'),
            blockTopPanel = container.getParent('.b-cec_info'),
            opacity,
            marginTop,
            domain,
            state;


        if (container.hasClass('hidden')) {
            container
                .removeClass('hidden')
                .setStyle('margin-top', -content.getCoordinates().height - 50);

            marginTop = 0;
            state = '';
            blockTopPanel.addClass('opened');
        } else {
            //opacity = 0;
            marginTop = -content.getCoordinates().height - 50;
            state = 'hidden';
            blockTopPanel.removeClass('opened');
        }

        if (globals.base_domain) {
            domain = '.' + globals.base_domain.url.split('//')[1];
        } else if (globals.domain) {
            domain = '.' + globals.domain.url.split('//')[1]
        }

        new Fx.Morph(container, {
            duration: 400,
            onComplete: function () {
                if (state == 'hidden') {
                    // Сохранение в куках состояния блока (раскрыт/скрыт)
                    Cookie.write('voted_users_state', state, {
                        duration: 365,
                        domain: domain
                    });
                    container.addClass('hidden');
                } else {
                    Cookie.dispose('voted_users_state', {
                        domain: domain
                    });
                }
            }
        }).start({
                //opacity: opacity
                marginTop: marginTop
            });
    },

    // Загрузка последних проголосовавших
    loadVotes: function () {
        new futuAjax({
            button: $('js-voted_users_list'),
            attribute: 'opacity',
            color_to: 1,
            color_from: 1,
            url: ajaxUrls.democracy_last_votes,
            data: '',
            type: 'GET',
            onLoadFunction: function (response) {
                if (response.votes) {
                    var votes = response.votes.reverse(),
                        add;
                    for (var i = 0, l = votes.length; i < l; i++) {
                        add = true;
                        for (var k = 0, kl = this.votedList.length; k < kl; k++) {
                            if (this.votedList[k].created_at == votes[i].created_at && this.votedList[k].domain.id == votes[i].domain.id && this.votedList[k].voter.id == votes[i].voter.id) {
                                add = false;
                            }
                        }
                        if (add) {
                            this.votedList.push(votes[i]);
                        }
                    }

                    if (this.firstLoad) {
                        this.firstLoad = false;
                        this.renderAllVotes();
                    }
                }
            }.bind(this)
        });
    },
    renderItem: function (data, animate) {
        var html = '<span class="b-voter">{voter}&nbsp;&rarr;</span> {candidate} <div><span>{date}</span>&nbsp; <a href="http://{domain}" class="b-sys_link">{domain_prefix}</a></div>'.substitute({
            date: new Date(data.created_at * 1000).format('HH:MM'),
            voter: data.voter.deleted == 1 ? '<span class="b-removed_user">' + data.voter.login + '</span>' : '<a href="' + globals.parent_site + '/user/' + data.voter.login + '">' + data.voter.login + '</a>',
            candidate: data.user.deleted == 1 ? '<span class="b-removed_user b-candidate">' + data.user.login + '</span>' : '<a href="' + globals.parent_site + '/user/' + data.user.login + '" class="b-candidate">' + data.user.login + '</a>',
            domain: data.domain.url,
            domain_prefix: data.domain.url.split('.')[0],
            parent_site: globals.parent_site
        });
        var infoEl = new Element('div', {
            'class': 'b-elections_voting_data',
            html: html
        });
        var infoElHeight;
        if (animate) {
            infoEl.setStyles({
                position: 'absolute',
                top: -10000
            });
        }

        infoEl.inject(this.infoContainer, 'top');

        if (animate) {
            infoElHeight = infoEl.getCoordinates().height;
            infoEl.setStyles({
                position: null,
                top: null,
                marginTop: -infoElHeight,
                opacity: 0
            });
            new Fx.Morph(infoEl, {
                duration: 400,
                onComplete: function () {
                    infoEl.erase('style');
                }
            }).start({
                    marginTop: 0,
                    opacity: 1
                });
        }
    },

    renderVotes: function () {
        if (this.votedList[this.currentIndex + 1]) {
            this.currentIndex = this.currentIndex + 1;
            this.renderItem(this.votedList[this.currentIndex], true);

            // Удаление находящихся в списке элементов, если общее количество элементов превысило лимит
            var visibleElements = this.infoContainer.getElements('.b-elections_voting_data');
            visibleElements.each(function (visibleElementsItem, k) {
                if (k >= this.limit) {
                    new Fx.Morph(visibleElementsItem, {
                        duration: 400,
                        onComplete: function () {
                            visibleElementsItem.destroy();
                        }
                    }).start({
                            marginBottom: -visibleElementsItem.getCoordinates().height,
                            opacity: 0
                        });
                }
            }.bind(this));
        }
    },

    renderAllVotes: function () {
        for (var i = 0, l = this.votedList.length; i < l; i++) {
            if (i == this.limit) {
                break;
            }
            this.currentIndex = i;
            this.renderItem(this.votedList[i]);
        }
    }
};

var liveStreamHandler = {
    posts: [],
    renderTimeout: 5000,
    requestTimeout: 5000,
    postsLimit: 42,
    sendNewRequest: true,

    init: function () {
        this.container = $('js-posts_holder');
        this.preloadContainer = $('js-posts_preloader');

        setInterval(function () {
            this.renderPost();
        }.bind(this), this.renderTimeout);

        // Выполнение запроса за новыми постами через интервал
        setInterval(function () {
            if (this.sendNewRequest) {
                this.getNewPosts();
            }
        }.bind(this), this.requestTimeout);
    },

    // Получение списка постов, в которых появились новые комментарии
    getNewPosts: function () {
        this.sendNewRequest = false;

        new futuAjax({
            button: $('js-posts_holder'),
            color_to: '',
            color_from: '',
            url: ajaxUrls.live_stream,
            data: '',
            onLoadFunction: function (response) {
                var newPostsContainer = new Element('div', {
                    html: response.template
                });
                var newPosts = newPostsContainer.getElements('.post');
                var renderedPosts = this.container.getElements('.post');
                var addNewPost = true;

                // Добавление постов в очередь на отрисовку
                if (response.docs && response.docs.length == newPosts.length) {
                    for (var i = 0, l = newPosts.length; i < l; i++) {
                        newPosts[i].set('data-comments_qty', response.docs[i].comments_count);
                        // Если пост уже находится в очереди на отрисовку, он будет заменен постом из ответа
                        for (var j = 0, jl = this.posts.length; j < jl; j++) {
                            if (newPosts[i].get('id') == this.posts[j].get('id')) {
                                this.posts[j] = newPosts[i];
                                addNewPost = false;
                            }
                        }

                        // Если пост уже отрисован на странице и количество комментариев поста не изменилось,
                        // его не нужно добавлять в очередь
                        for (var k = 0, kl = renderedPosts.length; k < kl; k++) {
                            if ((newPosts[i].get('id') == renderedPosts[k].get('id')) && (renderedPosts[k].get('data-comments_qty') == newPosts[i].get('data-comments_qty'))) {
                                addNewPost = false;
                            }
                        }

                        if (addNewPost) {
                            this.posts.push(newPosts[i]);
                        }
                    }
                }
                this.sendNewRequest = true;
            }.bind(this),
            onCustomErrorFunction: function () {
                this.sendNewRequest = true;
            }.bind(this)
        });
    },

    renderPost: function () {
        if (this.posts.length > 0) {
            var post = this.posts.shift(),
                visiblePost = $(post.get('id')) ? $(post.get('id')).getParent('.b-post_container') : null,
                postContainer = new Element('div', {
                    'class': 'b-post_container'
                });

            // Специально созданный класс, чтобы иметь возможность пересчитывать
            // координаты попапа с результатами голосований на каждом шаге анимации
            Fx.StepMorph = new Class({
                Extends: Fx.Morph,
                render: function (element, property, value, unit) {
                    var ret = this.parent.apply(this, arguments);

                    // Если раскрыт попап с результатами голосований,
                    // нужно на каждом шаге перемещать его относительно поста, к которому он относится
                    var votesResultsPopup = $('js-votes_popup');

                    if (votesResultsPopup && !votesResultsPopup.hasClass('invisible')) {
                        var post = $(votesResultsPopup.get('data-el_id')),
                            postControls = post.getElement('.vote'),
                            popupControls = votesResultsPopup.getElement('.vote');
                        votesResultsPopup.setStyle('top', postControls.getCoordinates().top - popupControls.getCoordinates(votesResultsPopup).top);
                    }

                    return ret;
                }

            });

            // Анимация появления нового поста
            var postRenderAnimation = function () {
                new Fx.StepMorph(postContainer, {
                    duration: 222,
                    onComplete: function () {
                    }
                }).start({
                        marginTop: 0
                    });
            };

            if (visiblePost) {
                // Перемещение поста, который уже был отрисован на странице
                visiblePost.getElement('.dd').innerHTML = post.getElement('.dd').innerHTML;
                visiblePost.getElement('.post').set('data-comments_qty', post.get('data-comments_qty'));
                datesHandler.setDates();

                var visiblePostCoordinates = visiblePost.getCoordinates(this.container),
                    topPos = visiblePostCoordinates.top;

                // Если пост не находится в самом верху страницы, он поднимается наверх
                if (topPos > 0) {
                    // Создание элемента-заглушки по размеру видимого поста,
                    // который заполняет собою пространство на месте перемещаемого наверх контейнера
                    var transparentBtottomElement = new Element('div');

                    // Создание элемента-заглушки, который будет увеличиваться до размера анимируемого поста, освобождая для него пространство вверху контейнера
                    var transparentTopElement = new Element('div');
                    var siblingsPosts = visiblePost.getSiblings();

                    // Задаем полупрозрачность соседним постам
                    siblingsPosts.setStyles({
                        opacity: 0.6
                    });

                    transparentBtottomElement
                        .setStyles({
                            width: visiblePostCoordinates.width,
                            height: visiblePostCoordinates.height
                        })
                        .inject(visiblePost, 'after');

                    transparentTopElement
                        .inject(this.container, 'top');

                    visiblePost
                        .setStyles({
                            position: 'absolute',
                            display: 'block',
                            opacity: 1,
                            top: topPos,
                            zIndex: 2
                        })
                        .inject(this.container, 'top');

                    setTimeout(function () {
                        new Fx.Morph(visiblePost, {
                            duration: 400,
                            onComplete: function () {
                                transparentBtottomElement.destroy();
                                visiblePost.erase('style');
                            }.bind(this)
                        }).start({
                                top: 0
                            });

                        // Анимация увеличения верхнего контейнера-заглушки
                        new Fx.StepMorph(transparentTopElement, {
                            duration: 400,
                            onComplete: function () {
                                transparentTopElement.destroy();
                                visiblePost.erase('style');
                                // Убираем полупрозрачность у соседних постов
                                siblingsPosts.setStyles({
                                    opacity: null
                                });
                            }.bind(this)
                        }).start({
                                height: [0, visiblePostCoordinates.height]
                            });

                        // Анимация уменьшения верхнего контейнера-заглушки
                        new Fx.Morph(transparentBtottomElement, {
                            duration: 400
                        }).start({
                                height: 0
                            });
                    }.bind(this), 200);
                } else {
                    // Если пост — самый верхний пост на странице, анимацией показывается,
                    // что в нем обновилось количество комментариев
                    new Fx.Morph(visiblePost.getElement('.b-post_comments_links'), {
                        duration: 400,
                        transition: Fx.Transitions.Back.easeInOut,
                        onComplete: function () {
                            visiblePost.getElement('.b-post_comments_links').erase('style');
                        }.bind(this)
                    }).start({
                            opacity: [0.5, 1]
                        });
                }
            } else {
                post.inject(postContainer, 'top');
                postContainer.inject(this.preloadContainer, 'top');
                datesHandler.setDates();

                postContainer
                    .inject(this.container, 'top')
                    .setStyles({
                        display: 'block',
                        opacity: 1,
                        marginTop: -postContainer.getCoordinates().height
                    });
                postRenderAnimation();
            }

            // Удаление последнего элемента со страницы, если количество постов превысило лимит
            this.container.getElements('.post').each(function (item, i) {
                if (i + 1 > this.postsLimit) {
                    new Fx.Morph(item, {
                        duration: 222,
                        onComplete: function () {
                            item.destroy();
                        }.bind(this)
                    }).start({
                            height: 0,
                            opacity: 0
                        });
                }
            }.bind(this));
        }
    }
};
goldHandler = {
    validateAddUsers: function () {
        var target_users_element = $('js-golden_form_gift_target');
        if (target_users_element.getElement('input[name="users"]').value.trim() == 0) {
            ajaxHandler.highlightField(target_users_element.getElement('input[name="users"]'), '#FFFFFF');
            target_users_element.getElement('input[name="users"]').focus();
            return false;
        }
        return true;
    },
    addUsers: function () {
        if (goldHandler.validateAddUsers()) {
            goldHandler.calculatePrice();
        }
    },
    removeUser: function (button) {
        $(button).getParent('li').destroy();
        goldHandler.calculatePrice();
    },
    calculatePrice: function (get_data_username) {
        var data = '';
        data += 'months=' + $('js-golden_form_duration').value + '&';

        var target_users_element = $('js-golden_form_gift_target');
        var target_users_input_element = target_users_element.getElement('input[name="users"]');
        var target_users_input_value = target_users_input_element.value.trim();
        var chosen_users_elements = target_users_element.getElements('.b-golden_form_gift_target_list li');

        if (chosen_users_elements.length == 0 && target_users_input_value == 0) {
            $('js-golden_paying_checkout').addClass('hidden');
            ajaxHandler.highlightField(target_users_input_element, '#FFFFFF');
            target_users_element.getElement('.b-golden_form_gift_target_list').innerHTML = '';
            return false;
        }

        data += 'users=';

        if (get_data_username) {
            data += get_data_username;
            target_users_element.getElement('.b-golden_form_gift_target_list').innerHTML = '';
        } else {
            chosen_users_elements.each(function (chosen_user_element) {
                data += chosen_user_element.getElement('.b-golden_form_gift_target_list_user').innerHTML.trim() + ',';
            });
        }

        data += ',' + target_users_input_value;

        new futuAjax({
            button: target_users_element.getElement('.b-fui_icon_button_add'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.gold_calculate,
            data: data,
            onLoadFunction: function (response) {
                var price_rubles = Math.round(parseFloat(response.price));
                var price_rubles_caption = price_rubles + ' ' + utils.getPlural(price_rubles, ['рубль', 'рубля', 'рублей']);
                var users_names = response.users.split(',');
                var price_for_one_rubles = Math.round(price_rubles / users_names.length);
                var price_for_one_rubles_caption = price_for_one_rubles + ' ' + utils.getPlural(price_rubles, ['рубль', 'рубля', 'рублей']);

                var users_iHTML = '';
                var details_iHTML = '';

                $A(users_names).each(function (user_name) {
                    users_iHTML += '<li>\
						<a href="#" class="b-fui_icon_button b-fui_icon_button_close" onclick="goldHandler.removeUser(this); return false;"><span></span><em></em></a>\
						<a class="b-golden_form_gift_target_list_user" href="/user/{user_name}">{user_name}</a>\
					</li>'.substitute({
                            user_name: user_name
                        });
                    details_iHTML += '<p>{price_rubles_caption}&nbsp;&mdash; &laquo;Золотой dirty&raquo; для <a href="/user/{user_name}">{user_name}</a> на {gold_duration}.<input type="hidden" value="{user_name}"></p>'.substitute({
                        price_rubles_caption: price_for_one_rubles_caption,
                        user_name: user_name,
                        gold_duration: $('js-golden_form_duration').options[$('js-golden_form_duration').selectedIndex].innerHTML
                    });
                });

                if (!response.applicable_for_badge) {
                    $('js-golden_account_pin').checked = false;
                    $('js-golden_address').addClass('hidden');
                    $('js-golden_address').inject($('js-golden_address_hidden'));
                    $('js-golden_account_pin').getParent('p').addClass('hidden');
                } else {
                    if ($('js-golden_account_pin').checked) {
                        $('js-golden_address').removeClass('hidden');
                        $('js-golden_address').inject($('js-golden_address_checkout'));
                    } else {
                        $('js-golden_address').addClass('hidden');
                        $('js-golden_address').inject($('js-golden_address_hidden'));
                    }
                    $('js-golden_account_pin').getParent('p').removeClass('hidden');
                }

                $('js-golden_paying_checkout').removeClass('hidden');
                $('js-golden_paying_submit_amount_text').innerHTML = price_rubles_caption;
                target_users_input_element.value = '';
                target_users_element.getElement('.b-golden_form_gift_target_list').innerHTML = users_iHTML;
                $('js-golden_paying_submit_amount_details').innerHTML = details_iHTML;
                $('js-golden_account_users').value = response.users;
                $('js-golden_account_duration').value = $('js-golden_form_duration').value;
            }
        });
    },
    togglePinForm: function (checkbox_element) {
        if (checkbox_element.checked) {
            $('js-golden_address').removeClass('hidden');
            $('js-golden_address').inject($('js-golden_address_checkout'));
        } else {
            $('js-golden_address').addClass('hidden');
            $('js-golden_address').inject($('js-golden_address_hidden'));
        }
    },
    validateCheckout: function () {
        var form_data = $('js-gold_checkout_form').toQueryString().parseQueryString();
        var required_fields_empty = false;
        var at_least_one_required_field_filled = false;

        if (!$('js-golden_account_terms').checked) {
            new futuAlert('Вы должны согласиться с условиями.');
            return false;
        }

        if (form_data.pin) {
            var required_fields = ['address_name', 'address_zip_code', 'address_country', 'address_city', 'address_street', 'address_house'];

            $A(required_fields).each(function (field) {
                if (!form_data[field] || form_data[field].trim().length == 0) {
                    required_fields_empty = true;
                }
            });

            if (required_fields_empty) {
                new futuAlert('Вы должны заполнить все поля для адреса, это значительно увеличивает шансы получить наше письмо.');
                return false;
            }
        }

        return true;
    }
};

proAccountHandler = {
    updateSettings: function (input) {
        var data,
            value;
        if (input && input.name) {
            value = input.checked ? 0 : 1;
            data = input.name + '=' + value;
            new futuAjax({
                button: input,
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.profile_pro_settings,
                data: data,
                onLoadFunction: function (response) {
                },
                onCustomErrorFunction: function (response) {
                    input.set('checked', !input.checked);
                }
            });
        }
    },
    showRankForm: function () {
        $('js-ranks_no_rank').addClass('hidden');
        $('js-ranks_current_rank').removeClass('hidden');
        $('js-ranks_current_rank_text').addClass('hidden');
        $('js-ranks_new_rank').removeClass('hidden');
        $('js-ranks_new_rank').focus();
        $('js-ranks_new_rank').select();
    },
    validateRank: function () {
        if ($('js-ranks_new_rank').value.trim().length > 16) {
            ajaxHandler.highlightField($('js-ranks_new_rank'));
            new futuAlert('Звание по правилам не может быть длиннее 16 символов.');
            $('js-ranks_new_rank').focus();
            return false;
        }
        return true;
    },
    setRank: function () {
        if (proAccountHandler.validateRank()) {
            if ($('js-ranks_new_rank').value.trim().length == 0) {
                proAccountHandler.resetRank();
            } else {
                var data = 'rank=' + encodeURIComponent($('js-ranks_new_rank').value.trim());
                new futuAjax({
                    button: $('js-ranks_new_rank'),
                    attribute: 'opacity',
                    color_to: 0.5,
                    color_from: 1,
                    url: ajaxUrls.ranks_set,
                    data: data,
                    onLoadFunction: function (response) {
                        new futuAlert('У вас новое звание&nbsp;&mdash; ' + response.parced_rank);
                        $('js-ranks_current_rank_text').innerHTML = response.parced_rank;
                        $('js-ranks_current_rank_text').removeClass('hidden');
                        $('js-ranks_new_rank').addClass('hidden');
                    }
                });
            }
        }
    },
    resetRank: function () {
        var data = '';
        new futuAjax({
            button: $('js-ranks_current_rank').getElement('.b-pro_acc_rank_reset'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.ranks_reset,
            data: data,
            onLoadFunction: function (response) {
                $('js-ranks_no_rank').removeClass('hidden');
                $('js-ranks_current_rank').addClass('hidden');
            }
        });
    }
};
var admHandler = {
    userRegistered: false,
    year: null,
    formFields: [
        {
            name: 'address_name',
            text: 'имя получателя'
        },
        {
            name: 'phone_number',
            text: 'номер телефона'
        },
        {
            name: 'address_zip_code',
            text: 'индекс'
        },
        {
            name: 'address_country',
            text: 'страну'
        },
        {
            name: 'address_city',
            text: 'город или район'
        },
        {
            name: 'address_street',
            text: 'улицу'
        },
        {
            name: 'address_house',
            text: 'номер дома'
        }
    ],
    initialize: function (params) {
        this.userRegistered = params.userRegistered || false;
        this.year = params.year || null;
    },
    sendUserData: function () {
        var form,
            url,
            data,
            formData;

        if (this.userRegistered) {
            url = ajaxUrls.adm_edit_contacts;
        } else {
            url = ajaxUrls.adm_register;
        }

        if (admHandler.validateForm()) {
            form = $('js-adm_form');
            data = form.toQueryString();
            formData = data.parseQueryString();
            data += '&year=' + admHandler.year;

            new futuAjax({
                button: $('js-form_submit'),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: url,
                data: data,
                onLoadFunction: function (response) {
                    admHandler.userRegistered = true;
                    for (var i in formData) {
                        if (formData.hasOwnProperty(i)) {
                            if (i in response) {
                                var input = form.getElement('input[name="' + i + '"]');
                                formData[i] = response[i];
                                if (input) {
                                    input.value = response[i];
                                }
                            }
                        }
                    }
                    $('js-user_address').getElement('.b-user_address_content').innerHTML = '<h4><span class="b-subtitle">Моя заявка</span> <span class="b-subtitle b-subtitle_normal b-subtitle_black">{name}</span></h4>\
						<div class="b-user_address">{address_zip_code}, {address_country}, \
						{address_city}, {address_street}, {address_house}{address_block}{address_flat} \
						<br/>{phone_number}</div>'.substitute({
                            name: formData.address_name,
                            address_zip_code: formData.address_zip_code,
                            address_country: formData.address_country,
                            address_city: formData.address_city,
                            address_street: formData.address_street,
                            address_house: formData.address_house,
                            address_block: (formData.address_block.trim() != '' ? '/' : '') + formData.address_block,
                            address_flat: (formData.address_flat.trim() != '' ? '-' : '') + formData.address_flat,
                            phone_number: formData.phone_number
                        });
                    admHandler.showElement($('js-user_address'), $('js-adm_registration'));
                    admHandler.updateStatistics(parseInt(response.registered_users_count, 10));
                },
                onCustomErrorFunction: function () {
                    Recaptcha.reload('t');
                }
            });
        }

    },
    validateForm: function () {
        var form = $('js-adm_form'),
            formData = form.toQueryString().parseQueryString(),
            message = '',
            requiredFieldsEmpty = false,
            emptyFields = [];
        if (!$('js-adm_agreement').checked) {
            new futuAlert('Вы должны согласиться с условиями.');
            return false;
        }
        $A(admHandler.formFields).each(function (field) {
            if (!formData[field.name] || formData[field.name].trim().length == 0) {
                requiredFieldsEmpty = true;
                emptyFields.push(field.text);
            }
        });

        if (requiredFieldsEmpty) {
            message = 'Нужно указать ' + utils.getListAsString(emptyFields) + '.';
            new futuAlert(message);
            return false;
        }
        if ($('recaptcha_response_field').value.trim().length < 1) {
            new futuAlert('Будьте добры, присмотритесь к картинке и напишите два подходящих слова.');
            return false;
        }

        return true;
    },
    removeRegisterData: function () {
        var form = $('js-remove_registration_form'),
            data = form.toQueryString();

        if ($('recaptcha_response_field').value.trim().length < 1) {
            new futuAlert('Будьте добры, присмотритесь к картинке и напишите два подходящих слова.');
            return false;
        }
        data += '&year=' + admHandler.year;

        new futuAjax({
            button: $('js-remove_registration_form_submit'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.adm_remove_registration,
            data: data,
            onLoadFunction: function (response) {
                var admForm = $('js-adm_form');
                admHandler.userRegistered = false;
                admForm.getElements('input[type="text').each(function (input) {
                    input.value = '';
                });
                $('js-adm_agreement').set('checked', false);
                admHandler.showElement($('js-adm_registration'), $('js-remove_registration'));
                $('js-form_submit').innerHTML = 'записаться';
                $('js-cancel_editing').addClass('hidden');
                admHandler.updateStatistics(parseInt(response.registered_users_count, 10));
            },
            onCustomErrorFunction: function () {
                Recaptcha.reload('t');
            }
        });
    },

    showElement: function (el, visibleEl) {
        var captchaContainer = el.getElement('.b-captcha_container'),
            captchaField = $('js-captcha_field'),
            preloadContainer = $('js-adm_preload_container'),
            initialHeight,
            finalHeight;

        if (!visibleEl.hasClass('js-animation')) {
            visibleEl.addClass('js-animation');
            if (captchaContainer) {
                Recaptcha.reload('t');
                captchaField.inject(captchaContainer);
            }

            if (el.get('id') == 'js-adm_registration' && visibleEl.get('id') == 'js-user_address') {
                $('js-cancel_editing').removeClass('hidden');
                $('js-form_submit').innerHTML = 'сохранить';
            }
            el.inject(preloadContainer);
            el.removeClass('hidden');
            finalHeight = el.getCoordinates().height;
            initialHeight = visibleEl.getCoordinates().height;
            el.setStyles({
                opacity: 0,
                height: initialHeight
            });

            new Fx.Morph(visibleEl, {
                duration: 222,
                onComplete: function () {
                    visibleEl.addClass('hidden');
                    el.inject(preloadContainer, 'after');
                    new Fx.Morph(el, {
                        duration: 222,
                        onComplete: function () {
                            new Fx.Morph(el, {
                                duration: 222,
                                onComplete: function () {
                                    el.erase('style');
                                    visibleEl.removeClass('js-animation');
                                }.bind(this)
                            }).start({
                                    opacity: 1
                                });
                        }.bind(this)
                    }).start({
                            height: finalHeight
                        });
                }.bind(this)
            }).start({
                    opacity: 0
                });
        }
    },

    updateStatus: function (input) {
        var data = input.get('name') + '=' + input.checked + '&year=' + admHandler.year,
            parent = input.getParent('.b-present_status'),
            dateEl = parent.getElement('.js-date'),
            presentsStatEl = $('js-presents_stat'),
            stat = '';

        new futuAjax({
            button: input,
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.adm_update_status,
            data: data,
            onLoadFunction: function (response) {
                var date;
                if (response.present_received) {
                    date = response.present_received;
                } else if (response.present_sent) {
                    date = response.present_sent;
                }

                if (date) {
                    dateEl
                        .set('data-epoch_date', date)
                        .removeClass('js-date__formatted');
                    datesHandler.setDates();
                } else {
                    dateEl.innerHTML = '';
                    dateEl.set('data-epoch_date', '');
                }
                if (response.presents_sent) {
                    stat = response.presents_sent + ' ' + utils.getPlural(response.presents_sent, ['подарок отправлен', 'подарка отправлено', 'подарков отправлено'])
                    if (response.presents_received) {
                        stat += ',';
                    } else {
                        stat += '!';
                    }
                }
                if (response.presents_received) {
                    stat += ' ' + response.presents_received + ' уже ' + utils.getPlural(response.presents_received, ['получен', 'получено', 'получено']) + '!';
                }
                presentsStatEl.innerHTML = stat;
            },
            onCustomErrorFunction: function () {
                input.set('checked', !input.checked);
            }
        });
    },
    showAgreement: function (event) {
        var el = $('js-agreement_text'),
            content = el.getElement('.b-agreement_text'),
            initialHeight = el.hasClass('opened') ? content.getCoordinates().height : 0,
            finalHeight = el.hasClass('opened') ? 0 : content.getCoordinates().height;


        if (!el.hasClass('js-animation')) {
            el.addClass('js-animation');
            new Fx.Morph(el, {
                duration: 222,
                onComplete: function () {
                    el.toggleClass('opened', !el.hasClass('opened'));
                    el.erase('style');
                    el.removeClass('js-animation');
                }.bind(this)
            }).start({
                    height: [initialHeight, finalHeight]
                });
        }

    },
    updateStatistics: function (qty) {
        var el = $('js-adm_stat');
        if (qty > 0) {
            el.removeClass('hidden');
            el.innerHTML = '<h4><span class="b-subtitle b-subtitle_black">{playing}  уже <span class="b-subtitle_info">{qty} </span></span></h4>'.substitute({
                playing: utils.getPlural(qty, ['играет', 'играют', 'играют']),
                qty: qty + ' ' + utils.getPlural(qty, ['человек', 'человека', 'человек'])

            })
        } else {
            el.addClass('hidden');
        }
    }
};
var userAdsHandler = {
    price: null,
    hide: function (el) {
        var container = el.getParent(),
            initialHeight;

        new Fx.Morph(container, {
            duration: 222,
            onComplete: function () {
                initialHeight = container.getCoordinates().height;
                container.setStyles({
                    padding: 0,
                    height: initialHeight
                });
                new Fx.Morph(container, {
                    duration: 222,
                    onComplete: function () {
                        container.destroy();
                    }.bind(this)
                }).start({
                        height: 0
                    });
            }.bind(this)
        }).start({
                opacity: 0
            });
    },
    validateAdForm: function () {
        var adBodyInput = $('js-ad_body'),
            adAgreementInput = $('js-ad_agreement');

        if (!adBodyInput || adBodyInput.value.trim().length < 1) {
            adBodyInput.focus();
            new futuAlert('Введите, пожалуйста, текст объявления.');
            return false;
        }
        if (!adAgreementInput || !adAgreementInput.checked) {
            new futuAlert('Вы должны согласиться с условиями.');
            return false;
        }
        return true;
    },
    showPreview: function (userLogin, price) {
        var form = $('js-ad_form'),
            data = form.toQueryString(),
            previewEl = $('js-ad_preview'),
            previewContainer = $('js-ad_preview_content'),
            wrapper = previewEl.getElement('.b-ad_preview_container'),
            adBodyInput = $('js-ad_body');

        this.price = price || 0;
        if (userAdsHandler.validateAdForm()) {
            new futuAjax({
                button: $('js-new_post_preview'),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.user_ad_preview,
                data: data,
                onLoadFunction: function (response) {
                    previewContainer.innerHTML = '<div class="b-user_ad">{body}\
											<div class="c_footer">Объявление от <a href="/user/{user_login}" class="c_user">{user_login}</a></div>\
										</div>'.substitute({
                            body: response.url ? '<a href="' + response.url + '" class="b-ad_link" target="_blank">' + response.body + '</a>' : response.body,
                            user_login: userLogin
                        });
                    if (previewEl.hasClass('hidden')) {
                        previewEl
                            .setStyles({
                                overflow: 'hidden',
                                height: 0,
                                opacity: 0
                            })
                            .removeClass('hidden');

                        new Fx.Morph(previewEl, {
                            duration: 222,
                            onComplete: function () {
                                new Fx.Morph(previewEl, {
                                    duration: 222,
                                    onComplete: function () {
                                        previewEl.erase('style');
                                    }
                                }).start({
                                        opacity: 1
                                    });
                            }
                        }).start({
                                height: wrapper.getCoordinates().height
                            });
                    }
                }
            });
        }
    },

    calculatePrice: function () {
        var durationInput = $('js-ad_duration'),
            daysText = $$('.b-days_text')[0],
            sumEl = $('js-paying_submit_amount_text'),
            days = parseInt(durationInput.value, 10),
            sum = days * parseInt(this.price, 10);

        sumEl.innerHTML = sum && sum > 0 ? ('С вас&nbsp;&mdash; ' + sum + ' ' + utils.getPlural(sum, ['рубль', 'рубля', 'рублей']) + '!') : '';
        daysText.innerHTML = days > 0 ? utils.getPlural(days, ['сутки', 'суток', 'суток']) : '';
    },

    removeAd: function (el, id) {
        var item = el.getParent('li'),
            data = 'ad_id=' + id,
            message;
        if (item.hasClass('b-user_ad_list_item_inactive')) {
            message = 'Вы уверены, что хотите удалить объявление?';
        } else {
            message = 'При удалении объявления деньги не возвращаются. Удаляем?';
        }
        new futuDialogPopup({
            text: message,
            type: 'confirm',
            callback: function () {
                new futuAjax({
                    button: el,
                    attribute: 'opacity',
                    color_to: 0.5,
                    color_from: 1,
                    url: ajaxUrls.user_ad_delete,
                    data: data,
                    onLoadFunction: function (response) {
                        new Fx.Morph(item, {
                            duration: 222,
                            onComplete: function () {
                                new Fx.Morph(item, {
                                    duration: 222,
                                    onComplete: function () {
                                        item.destroy();
                                        userAdsHandler.updateListItems();
                                    }
                                }).start({
                                        height: 0
                                    });
                            }
                        }).start({
                                opacity: 0
                            });
                    }
                });
            }
        });
    },
    restart: function (el, login, price) {
        var item = el.getParent('.b-user_ad_list_item'),
            bodyEl = item.getElement('.b-ad_link');

        $('js-ad_body').value = bodyEl.innerHTML.trim();
        $('js-ad_link').value = bodyEl.href ? bodyEl.href : '';
        $('js-ad_duration').value = 1;
        $('js-ad_agreement').set('checked', true);
        this.showPreview(login, price);
    },

    toggleListItems: function (el) {
        var items = $$('.b-user_ad_list_item'),
            show = !el.hasClass('opened');

        items.each(function (item, i) {
            if (i > 2) {
                item.toggleClass('hidden', !show);
            }
        });
        el.toggleClass('opened', show);

        if (show) {
            el.innerHTML = 'свернуть';
        } else {
            el.innerHTML = 'вся история';
        }
    },

    updateListItems: function () {
        var el = $('js-list_more_button'),
            parent = el.getParent('.b-user_ad_list_more'),
            items = $$('.b-user_ad_list_item'),
            show = el.hasClass('opened');

        if (el) {
            if (items.length > 3) {
                items.each(function (item, i) {
                    if (i > 2) {
                        item.toggleClass('hidden', !show);
                    } else {
                        item.removeClass('hidden');
                    }
                });
            } else {
                parent.destroy();
                items.each(function (item, i) {
                    item.removeClass('hidden');
                });
            }
        }
    },

    retryPayment: function (id) {
        var form = $('js-ad_repayment_form'),
            purchaseIdInput = form.getElement('input[name="purchase_id"]');

        purchaseIdInput.value = id;
        form.submit();
    },

    countCharacters: function (el) {
        var parent = el.getParent(),
            descriptionEl = parent.getElement('.b-form_field_description'),
            maxLength = 120,
            length = el.value.length,
            message = 'Максимальная длина ' + maxLength + ' знаков';

        if (length == 0) {
            message += '.';
        } else if (maxLength - length >= 0) {
            message += ', осталось ' + (maxLength - length) + '.';
        }
        descriptionEl.innerHTML = message;
    },

    showAd: function () {
        var ad = $('js-posts_ad');

        if (ad) {
            ad.removeClass('hidden');
        }
    }
};
promoHandler = {
    subtitles: [
        'Выборы для всех!',
        'Electing people',
        'Вся власть трудящимся!'
    ],
    titleAnimation: false,
    renderPage: function () {
        if (globals.uri_directory[1] == 'comics' && !globals.uri_directory[2]) {
            URIHandler.navigateToPage($$('a.b-blog_nav_sort_link[href="/democracy/"]')[0]);
        }
        if (globals.uri_directory[1] == 'manifest') {
            URIHandler.navigateToPage($$('a.b-menu_link[href="/democracy/blogs/manifest/"]')[0]);
        }
        this.renderPageNavigation();
        this.renderContent();

        if (globals.uri_directory[1]) {
            if (globals.uri_directory[1] == 'blogs') {
                this.renderDomainsContent();
                this.renderSortNavigation();
                if (globals.uri_directory[2] != 'manifest') {
                    this.loadDomains();
                }
            } else if (globals.uri_directory[1] == 'cik') {
                democracyCikHandler.cikSectionInit();
            } else if (globals.uri_directory[1] == 'comics') {
                if (globals.uri_directory[2] == 'new' || globals.uri_directory[2] == 'top') {
                    this.renderComicsNavigation();
                    this.loadComics();
                }
            }
        } else {
            this.renderComicsNavigation();
            this.loadComics();
        }
    },
    renderPageNavigation: function () {
        var section = globals.uri_directory[1] ? globals.uri_directory[1] : '';
        var buttons = $$('.b-promo_menu_list_item_text[data-section="' + section + '"]');

        // для ссылок адривера или когда открыта страница /democracy
        if ((globals.uri_directory[1] == 'nokia' && globals.uri_directory[2] == 'comics') || !globals.uri_directory[1]) {
            buttons = $$('.b-promo_menu_list_item_text[data-section="comics"]');
        }

        if (buttons.length > 0) {
            this.selectItem(buttons[0]);
        }
    },

    renderSortNavigation: function () {
        var buttons = $$('.b-menu_link[href="' + globals.uri_directory_to_string + '"]');
        sortNavigationHandler.selectItem(buttons[0]);
    },

    renderComicsNavigation: function () {
        var buttons = $$('.b-blog_nav_sort_link[href="/' + globals.uri_directory.slice(0, 3).join('/') + '/"], .b-blog_nav_sort_link[href="' + document.location.href + '"]');
        if (buttons.length > 0) {
            modeNavigationHandler.selectItem(buttons[0]);
        } else {
            var first_button_element = $$('.b-blog_nav_sort_link')[0];
            modeNavigationHandler.selectItem(first_button_element);
        }
    },

    loadDomains: function (load_more_element) {
        var load_more = false;
        var navigation_id = globals.uri_directory.slice(0, 3).join('_');
        var sort = '';

        if (navigation_id == 'democracy_blogs') {
            sort = 'popular_blogs';
        } else if (navigation_id == 'democracy_blogs_stable') {
            sort = 'regular_blogs';
        }
        if (load_more_element) {
            load_more = true;
        } else {
            load_more_element = $$('*[data-navigation_id="' + navigation_id + '"] .b-blogs_list')[0];
        }

        moreHandler.loadDomains(load_more_element, {
            load_more: load_more,
            type: 'elections',
            sort: sort
        });
    },

    renderDomainsContent: function () {
        if (globals.uri_directory[2] == 'manifest') {
            $$('.b-promo_elections_info').addClass('hidden');
        } else {
            $$('.b-promo_elections_info').removeClass('hidden');
        }

        /*$A($$('*[data-navigation_id]')).each(function (page_section_element) {
         var section_navigation_id = page_section_element.getAttribute('data-navigation_id');
         var section_only_on_index = page_section_element.getAttribute('data-navigation_index_only');

         var new_navigation_id = globals.uri_directory.slice(1, 3).join('_');

         if (section_navigation_id == new_navigation_id) {
         page_section_element.removeClass('hidden');
         } else {
         page_section_element.addClass('hidden');
         }
         });*/
    },

    loadComics: function (load_more_element) {
        var load_more = false;
        var navigation_id = globals.uri_directory.slice(0, 3).join('_');
        var sort = 'popular';

        if (navigation_id == 'democracy_comics_new') {
            sort = 'new';
        } else if (navigation_id == 'democracy_comics_top') {
            sort = 'top';
        } else {
            navigation_id = 'democracy'
        }

        if (load_more_element) {
            load_more = true;
        } else {

            $$('.b-comics_list').getElements('.post').each(function (post_element) {
                post_element.destroy();
            });
            $$('.b-comics_list').getElements('.b-comics_list_page').each(function (page_element) {
                page_element.destroy();
            });

            load_more_element = $$('*[data-navigation_id="' + navigation_id + '"] .b-comics_list')[0];
        }

        democracyComicsHandler.loadComics(load_more_element, {
            load_more: load_more,
            sort: sort
        });
    },

    renderContent: function () {
        $A($$('*[data-navigation_id]')).each(function (page_section_element) {
            var section_navigation_id = page_section_element.getAttribute('data-navigation_id');
            var section_only_on_index = page_section_element.getAttribute('data-navigation_index_only');

            var new_navigation_id = globals.uri_directory.join('_');

            if (section_only_on_index) {
                if (section_navigation_id == new_navigation_id) {
                    page_section_element.removeClass('hidden');
                } else {
                    page_section_element.addClass('hidden');
                }
            } else {
                for (var i = 0; i < globals.uri_directory.length; i++) {
                    var new_navigation_id = '';
                    for (var j = 0; j <= i; j++) {
                        if (j > 0) {
                            new_navigation_id += '_';
                        }
                        new_navigation_id += globals.uri_directory[j];

                        if (section_navigation_id == new_navigation_id) {
                            page_section_element.removeClass('hidden');
                            break;
                        } else {
                            page_section_element.addClass('hidden');
                        }
                    }
                }
            }
        });
        var uriDirectory = globals.uri_directory[1] || 'comics',
            pageContents = $$('.b-promo_page_content[data-section="' + uriDirectory + '"]'),
            visibleContainer = $$('.b-promo_page_content.visible')[0],
            pageContent;

        // для ссылок адривера
        if (globals.uri_directory[1] == 'nokia' && globals.uri_directory[2] == 'nokia') {
            uriDirectory = 'comics';
            pageContents = $$('.b-promo_page_content[data-section="' + uriDirectory + '"]');
        }

        // Главная страница
        if (uriDirectory == 'nokia') {
            carouselHandler.init();
        }
        if (pageContents.length > 0) {
            pageContent = pageContents[0];

            if (visibleContainer && visibleContainer == pageContent) {
                return false;
            }

            var showContent = function () {
                pageContent
                    .setStyle('opacity', 0)
                    .addClass('visible')
                    .removeClass('invisible');
                new Fx.Morph(pageContent, {
                    duration: 222
                }).start({
                        opacity: 1
                    });
            };

            if (visibleContainer) {
                new Fx.Morph(visibleContainer, {
                    duration: 100,
                    onComplete: function () {
                        visibleContainer
                            .removeClass('visible')
                            .addClass('invisible');
                        showContent();
                    }
                }).start({
                        opacity: 0
                    });
            } else {
                showContent();
            }
        }
    },

    selectItem: function (button_element) {
        var item = button_element.getParent('.b-promo_menu_list_item');
        button_element = $(button_element);

        item
            .addClass('active')
            .getSiblings()
            .removeClass('active');
    },

    renderSubtitle: function () {
        var subtitle = $('js-slogan'),
            randomNum = Math.floor(Math.random() * this.subtitles.length);

        if (!this.titleAnimation) {
            this.titleAnimation = true;
            if (subtitle.innerHTML.trim() == this.subtitles[randomNum]) {
                randomNum = this.subtitles[randomNum + 1] ? randomNum + 1 : randomNum - 1;
            }
            new Fx.Morph(subtitle, {
                duration: 222,
                onComplete: function () {
                    subtitle.innerHTML = this.subtitles[randomNum];
                    new Fx.Morph(subtitle, {
                        duration: 100,
                        onComplete: function () {
                            setTimeout(function () {
                                this.titleAnimation = false;
                            }.bind(this), 500);
                        }.bind(this)
                    }).start({
                            opacity: 1
                        });
                }.bind(this)
            }).start({
                    opacity: 0
                });
        }
    }
};

var democracyCikHandler = {
    // firstLoad = true — результаты голосований все единовременно отрисовываются на странице.
    // firstLoad = false — результаты голосований появляются последовательно
    firstLoad: true,
    listByDate: {},
    existedResults: {},
    dates: [],
    offset: 0,

    cikSectionInit: function () {
        this.container = $('js-elections_voting_list');
        this.domainDescriptionContainer = $('js-elections_voting_list_description');
        this.searchInput = $('js-domains_search_input');
        this.loadMoreButton = $('js-load_more_votes_button');
        this.domainsList = $('js-controls_domains_list');

        // Очищение контейнера для отрисовки целиком всех результатов
        this.container.empty();
        this.listByDate = {};
        this.existedResults = {};
        this.firstLoad = true;
        this.dates = [];
        this.domainName = null;
        this.searchInput.value = '';
        this.domainDescriptionContainer
            .empty()
            .addClass('hidden');
        this.container.removeClass('b-selected_domain');


        // Загрузка результатов голосований
        this.loadVotes();

        if (this.domainsList.innerHTML.trim() == '') {
            // Загрузка списка сайтов
            this.loadDemocracyDomains();
        }

        // Загрузка результатов голосований через интервал, для постоянного обновления результатов на странице
        this.votesRequestInterval = setInterval(function () {
            if (!$$('.b-promo_page_content[data-section=cik]')[0].hasClass('visible') && this.votesRequestInterval) {
                clearInterval(this.votesRequestInterval);
            } else {
                this.loadVotes();

            }
        }.bind(this), 5000);

        this.votesRenderInterval = setInterval(function () {
            if (!$$('.b-promo_page_content[data-section=cik]')[0].hasClass('visible') && this.votesRenderInterval) {
                clearInterval(this.votesRenderInterval);
            } else {
                this.renderVotes();
            }
        }.bind(this), 2000);
    },

    setFilterDomain: function (event) {
        event.stopPropagation();
        var input = $('js-domains_search_input');

        if (event.type == 'keydown' && event.code == 13) {
            democracyCikHandler.filterByDomain();
        }
    },

    // Проставление значение в поле ввода при выборе домена в облаке
    changeFilterDomain: function (event) {
        event.preventDefault();
        this.searchInput.value = event.target.innerHTML;
        this.filterByDomain();
    },

    filterByDomain: function () {
        var input = $('js-domains_search_input');
        this.domainName = input.value;

        this.container.empty();
        this.listByDate = {};
        this.existedResults = {};
        this.firstLoad = true;
        this.dates = [];
        this.domainDescriptionContainer
            .empty()
            .addClass('hidden');
        this.loadMoreButton.addClass('hidden');
        this.offset = 0;

        this.loadVotes();
    },

    renderRow: function (time, position) {
        var position = position || 'top';
        var row = new Element('div', {
            'class': 'b-elections_voting_item'
        });
        var dateColumn = new Element('div', {
            'class': 'b-date_column',
            html: new Date(parseInt(time, 10)).format('d mmmm')
        });
        var itemsContainer = new Element('div', {
            'class': 'b-elections_voting'
        });


        row.inject(this.container, position);
        row.set('data-date', time);
        dateColumn.inject(row);
        itemsContainer.inject(row);
    },

    renderItem: function (data, container, position) {
        var position = position || 'top';
        var item = new Element('div', {
            'class': 'b-elections_voting_data',
            html: '<span>{date}</span>&nbsp;<span class="b-voter">{voter}&nbsp;&rarr;</span>\
									{candidate} <span class="b-domain_link">(<a href="http://{domain}" class="b-sys_link">{domain_prefix}</a>)</span>'.substitute({
                    date: new Date(data.created_at * 1000).format('HH:MM'),
                    voter: data.voter.deleted == 1 ? '<span class="b-removed_user">' + data.voter.login + '</span>' : '<a href="/user/' + data.voter.login + '">' + data.voter.login + '</a>',
                    candidate: data.user.deleted == 1 ? '<span class="b-removed_user b-candidate">' + data.user.login + '</span>' : '<a href="/user/' + data.user.login + '" class="b-candidate">' + data.user.login + '</a>',
                    domain: data.domain.url,
                    domain_prefix: data.domain.url.split('.')[0]
                })
        });

        if (position == 'top') {
            item.setStyle('opacity', 0);
        }

        item.inject(container, position);

        if (position == 'top') {
            new Fx.Morph(item, {
                duration: 400,
                onComplete: function () {
                    item.erase('style');
                }
            }).start({
                    opacity: 1
                });
        }
    },

    renderVotes: function (position) {
        var itemsContainer,
            row;

        for (var j = 0; j < this.dates.length; j++) {
            for (var k in this.listByDate) {
                if (this.listByDate.hasOwnProperty(k) && k == this.dates[j]) {
                    if ($$('.b-elections_voting_item[data-date=' + k + ']').length == 0) {
                        this.renderRow(k, position);
                    }
                    row = $$('.b-elections_voting_item[data-date=' + k + ']')[0];
                    itemsContainer = row.getElement('.b-elections_voting');

                    for (var i = 0, l = this.listByDate[k].length; i < l; i++) {
                        this.renderItem(this.listByDate[k][i], itemsContainer, position);
                    }
                    // Очищаем массив
                    this.listByDate[k] = [];
                }
            }
        }
    },

    // Загрузка последних проголосовавших
    loadVotes: function (button) {
        var data = '',
            color_to = 1,
            color_from = 1,
            date,
            key,
            loadMore;

        if (button) {
            color_to = 0.5;
            color_from = 1;
            data = 'offset=' + this.offset;
            loadMore = true;
        } else {
            button = $$('.b-promo_menu_list_item_text[data-section=cik]')[0]
        }

        if (this.domainName) {
            if (data != '') {
                data += '&';
            }
            data += 'domain=' + this.domainName;
        }
        new futuAjax({
            button: button,
            attribute: 'opacity',
            color_to: color_to,
            color_from: color_from,
            url: ajaxUrls.democracy_last_votes,
            data: data,
            type: 'GET',
            onLoadFunction: function (response) {
                if (response.votes) {
                    // Сортировка голосов по дате
                    for (var i = 0, l = response.votes.length; i < l; i++) {
                        key = response.votes[i].created_at + '-' + response.votes[i].voter.id + '-' + response.votes[i].domain.id;
                        if (!this.existedResults[key]) {
                            date = new Date(response.votes[i].created_at * 1000);

                            if (!this.listByDate[date.zeroTime().getTime()]) {
                                this.listByDate[date.zeroTime().getTime()] = [];
                                this.dates.push(date.zeroTime().getTime());
                            }
                            this.listByDate[date.zeroTime().getTime()].push(response.votes[i]);
                            this.existedResults[key] = response.votes[i];
                        }
                    }
                }

                if (response.domain) {
                    this.renderDomainDescription(response.domain);
                } else {
                    this.container.removeClass('b-selected_domain');
                }

                if (this.firstLoad || loadMore) {
                    // Если список загружается в первый раз или загружаются более ранние голоса, нужно их отрендерить отдельно и сразу все без анимации
                    this.offset = response.offset ? response.offset : 0;
                    this.loadMoreButton.toggleClass('hidden', this.offset == 0);
                    this.renderVotes('bottom');
                    this.firstLoad = false;
                }
            }.bind(this)
        });
    },

    loadDemocracyDomains: function () {
        moreHandler.loadDomains($$('.b-cloud_more_button')[0], {
            load_more: true,
            type: 'elections',
            sort: 'popular_blogs',
            list: true,
            onclick: 'onclick="var e = new Event(event);democracyCikHandler.changeFilterDomain(e);"'
        });
    },

    renderDomainDescription: function (domain) {
        if (this.domainDescriptionContainer.hasClass('hidden')) {
            var readersCount = domain.readers_count ? domain.readers_count : 0,
                electionsCount = domain.elections_count ? domain.elections_count : 0;
            var html = '<a href="http://{url}" class="b-elections_voting_domain"><span class="b-cec_title_text">{url}</span></a>\
							<span class="b-elections_voting_domain_description">{title}</span>\
						<div class="b-elections_voting_domain_statistics">{subscribers}, {elections}.</div>'.substitute({
                    url: domain.url ? domain.url : '',
                    title: domain.title ? domain.title : '',
                    subscribers: readersCount + ' ' + utils.getPlural(readersCount, ['подписчик', 'подписчика', 'подписчиков']),
                    elections: electionsCount > 0 ? 'выборы проводились ' + electionsCount + ' ' + utils.getPlural(electionsCount, ['раз', 'раза', 'раз']) : 'выборы еще не проводились'
                });

            this.domainDescriptionContainer
                .empty()
                .removeClass('hidden');
            this.domainDescriptionContainer.innerHTML = html;
            this.container.addClass('b-selected_domain');
        }
    }

};

democracyComicsHandler = {
    toggleComicsDescription: function (button) {
        var description_element = $('js-promo_comics_description');
        var inner_element = description_element.getElement('div');
        var inner_element_height = inner_element.offsetHeight + 'px';

        if ($(button).hasClass('b-promo_page_comics_toggle_description_active')) {

            description_element.style.maxHeight = $('js-promo_comics_description').getElement('div').offsetHeight + 'px';

            (function () {
                description_element.set('styles', {
                    '-webkit-transition': 'max-height 0.2s linear',
                    '-moz-transition': 'max-height 0.2s linear',
                    '-o-transition': 'max-height 0.2s linear',
                    'transition': 'max-height 0.2s linear'
                });
                description_element.style.maxHeight = '0px';
                inner_element.style.marginTop = '-' + inner_element_height;
            }).delay(100);

        } else {

            if (!inner_element.hasClass('js-transition_enabled')) {
                inner_element.addClass('js-transition_enabled');
                inner_element.style.marginTop = '-' + inner_element_height;
                (function () {
                    inner_element.set('styles', {
                        '-webkit-transition': 'margin-top 0.2s linear',
                        '-moz-transition': 'margin-top 0.2s linear',
                        '-o-transition': 'margin-top 0.2s linear',
                        'transition': 'margin-top 0.2s linear'
                    });
                    description_element.style.maxHeight = $('js-promo_comics_description').getElement('div').offsetHeight + 'px';
                    inner_element.style.marginTop = '0px';

                    (function () {
                        description_element.set('styles', {
                            '-webkit-transition': 'none',
                            '-moz-transition': 'none',
                            '-o-transition': 'none',
                            'transition': 'none'
                        });
                        description_element.style.maxHeight = 'none';
                    }).delay(200);

                }).delay(100);
            } else {
                description_element.set('styles', {
                    '-webkit-transition': 'max-height 0.2s linear',
                    '-moz-transition': 'max-height 0.2s linear',
                    '-o-transition': 'max-height 0.2s linear',
                    'transition': 'max-height 0.2s linear'
                });
                (function () {
                    description_element.style.maxHeight = $('js-promo_comics_description').getElement('div').offsetHeight + 'px';
                    inner_element.style.marginTop = '0px';
                }).delay(100);
            }

        }
        $(button).toggleClass('b-promo_page_comics_toggle_description_active');
    },
    initBalloons: function (balloons_holder) {
        var textarea_elements = [];
        if (balloons_holder.hasClass('b-promo_page_comics_frame')) {
            textarea_elements = balloons_holder.getElements('textarea');
        } else {
            textarea_elements = balloons_holder.getElements('.b-promo_page_comics_frame textarea')
        }

        textarea_elements.each(function (textarea_element) {
            textarea_element.value = textarea_element.value.replace(/<br>/gi, '\n');

            new DynamicTextarea(textarea_element, {
                maxRows: textarea_element.getAttribute('data-balloon_lines'),
                onShowError: function () {
                    var textarea_element = $(this.textarea);
                    var frame_element = textarea_element.getParent('.b-promo_page_comics_frame');
                    frame_element.addClass('b-promo_page_comics_frame_error');
                    textarea_element.addClass('error');
                },
                onHideError: function () {
                    var textarea_element = $(this.textarea);
                    var frame_element = textarea_element.getParent('.b-promo_page_comics_frame');
                    textarea_element.removeClass('error');
                    if (!frame_element.getElement('.error')) {
                        frame_element.removeClass('b-promo_page_comics_frame_error');
                    }
                }
            });
        });
    },
    checkBalloonSize: function (textarea) {
        var balloon_element = $(textarea).getParent('.b-promo_page_comics_frame_balloon');
        var balloon_size = balloon_element.getSize();
        var textarea_size = textarea.getSize();
        var textarea_scroll_size = textarea.getScrollSize();
        if (textarea_scroll_size.y > balloon_size.y || textarea_scroll_size.x > balloon_size.x) {
            balloon_element.addClass('b-promo_page_comics_frame_balloon_error');
        } else {
            balloon_element.removeClass('b-promo_page_comics_frame_balloon_error');
        }
    },
    comics_form_toggle_timeout: null,
    toggleNewComicsForm: function (button) {
        if (!button.hasClass('js-promo_new_comics_opened')) {
            $clear(democracyComicsHandler.comics_form_toggle_timeout);

            $('js-promo_new_comics').style.height = $('js-promo_new_comics').getElement('.b-promo_comics_new_comics').offsetHeight + 'px';

            democracyComicsHandler.comics_form_toggle_timeout = (function () {
                $('js-promo_new_comics').set('styles', {
                    '-webkit-transition': 'none',
                    '-moz-transition': 'none',
                    '-o-transition': 'none',
                    'transition': 'none'
                });
                $('js-promo_new_comics').style.height = 'auto';
                $('js-promo_new_comics').style.overflow = 'visible';
                button.addClass('js-promo_new_comics_opened');
            }).delay(200);

            if (!$('js-promo_new_comics').hasClass('js-promo_new_comics_drag_inited')) {
                $('js-promo_new_comics').addClass('js-promo_new_comics_drag_inited');
                (function () {
                    democracyComicsHandler.initNewComicsFrames();
                }).delay(500);

                new DynamicTextarea($('js-new_post_body'), {padding: 4});
            }
        } else {
            $clear(democracyComicsHandler.comics_form_toggle_timeout);

            $('js-promo_new_comics').style.height = $('js-promo_new_comics').getElement('.b-promo_comics_new_comics').offsetHeight + 'px';
            $('js-promo_new_comics').style.overflow = 'hidden';

            democracyComicsHandler.comics_form_toggle_timeout = (function () {
                $('js-promo_new_comics').set('styles', {
                    '-webkit-transition': 'height 0.2s linear',
                    '-moz-transition': 'height 0.2s linear',
                    '-o-transition': 'height 0.2s linear',
                    'transition': 'height 0.2s linear'
                });
                $('js-promo_new_comics').style.height = '0px';
                button.removeClass('js-promo_new_comics_opened');

            }).delay(200);

        }


    },
    initNewComicsFrames: function () {

        var droppables = $$('.b-promo_page_comics_new_selected_frames .b-promo_page_comics_frame');
        $$('.b-promo_comics_new_all_frames .b-promo_page_comics_frame_pic').each(function (frame) {
            frame.makeDraggable({
                droppables: droppables,
                onStart: function (draggable) {
                    draggable.setStyle('zIndex', '2');
                },
                onEnter: function (draggable, droppable) {
                    droppable.setStyle('border', '1px solid #035baa');
                    droppable.setStyle('zIndex', '1');
                },
                onLeave: function (draggable, droppable) {
                    droppable.setAttribute('style', '');
                    droppable.setStyle('zIndex', '0');
                },
                onDrop: function (draggable, droppable, event) {
                    event.preventDefault();
                    if (droppable) {
                        draggable.style.left = 0;
                        draggable.style.top = 0;
                        draggable.setStyle('zIndex', '0');

                        droppable.setAttribute('style', '');
                        droppable.className = draggable.getParent('.b-promo_page_comics_frame').className;
                        droppable.innerHTML = draggable.getParent('.b-promo_page_comics_frame').innerHTML;
                        democracyComicsHandler.initBalloons(droppable);
                    } else {
                        draggable.style.left = 0;
                        draggable.style.top = 0;
                    }
                },
                onCancel: function (draggable) {
                    democracyComicsHandler.addNextFrame(draggable.getParent('.b-promo_page_comics_frame'));
                }
            });
        });

    },
    removeComicsFrame: function (close_button_element) {
        close_button_element.getParent('.b-promo_page_comics_frame').style = '';
        close_button_element.getParent('.b-promo_page_comics_frame').className = 'b-promo_page_comics_frame';
        close_button_element.getParent('.b-promo_page_comics_frame').innerHTML = '';
    },
    addNextFrame: function (frame_element) {
        var droppables = $$('.b-promo_page_comics_new_selected_frames .b-promo_page_comics_frame');
        for (var i = 0; i < droppables.length; i++) {
            var droppable = droppables[i];
            if (!droppable.getElement('.b-promo_page_comics_frame_pic')) {
                droppable.setAttribute('style', '');
                droppable.className = frame_element.className;
                droppable.innerHTML = frame_element.innerHTML;
                democracyComicsHandler.initBalloons(droppable);
                break;
            }
        }

    },
    resetComicsForm: function () {
        $$('.b-promo_page_comics_new_selected_frames .b-promo_page_comics_frame').each(function (frame_element) {
            frame_element.style = '';
            frame_element.className = 'b-promo_page_comics_frame';
            frame_element.innerHTML = '';
        });
        $('js-new_post_body').value = '';
    },

    validateComics: function (form_element) {
        if (!form_element.getElement('.b-promo_page_comics_new_selected_frames .b-promo_page_comics_frame .b-promo_page_comics_frame_pic')) {
            new futuAlert('Выберите хотя бы один кадр для комикса.');
            return false;
        }
        if (form_element.getElement('.b-promo_page_comics_frame_error')) {
            new futuAlert('У вас в комиксе слишком много текста в балуне&nbsp;&mdash; проверьте, пожалуйста.');
            return false;
        }
        if (form_element.getElement('#js-promo_new_comics_terms') && !form_element.getElement('#js-promo_new_comics_terms').checked) {
            new futuAlert('Вы должны обязательно внимательно прочитать и согласиться с <a target="_blank" href="/democracy/comics/terms/">условиями конкурса</a>.');
            return false;
        }

        return true;
    },
    postComics: function (form_element) {
        if (democracyComicsHandler.validateComics(form_element)) {
            var data = '';
            var url = ajaxUrls.democracy_comics_add;
            if (form_element.getElement('input[name="id"]')) {
                data += 'id=' + form_element.getElement('input[name="id"]').value + '&';
                url = ajaxUrls.democracy_comics_edit;
            }

            var frames_elements = form_element.getElements('.b-promo_page_comics_new_selected_frames .b-promo_page_comics_frame');
            var frame_index = 0;
            for (var i = 0; i < frames_elements.length; i++) {
                var frame_element = frames_elements[i];
                var frame_pic_id = parseInt(frame_element.className.split('_')[frame_element.className.split('_').length - 1]);
                if (frame_pic_id > 0) {
                    frame_index++;
                    data += 'frame_' + frame_index + '_pic=' + frame_pic_id + '&';
                    frame_element.getElements('textarea').each(function (textarea_element, j) {
                        data += 'frame_' + frame_index + '_text_' + (j + 1) + '=' + encodeURIComponent(textarea_element.value) + '&';
                    });
                }
            }

            data += 'body=' + encodeURIComponent(form_element.getElement('textarea[name="body"]').value);

            new futuAjax({
                button: form_element.getElement('input[type="image"]'),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: url,
                data: data,
                onLoadFunction: function (response) {
                    window.location.href = '/democracy/comics/comments/' + response.post_id;
                }
            });
        }
    },
    loadComics: function (button, options) {
        var options = options || {};
        var data = '';
        var url = ajaxUrls.democracy_comics;
        var type = options.type ? options.type : '';
        var list_element = null;
        var more_button = null;
        var loading_animation_attribute = 'background-color';
        var loading_animation_color_to = Colors.links_system_color;
        var loading_animation_color_from = Colors.links_color;

        if (!options.load_more) {
            list_element = button;
            more_button = list_element.getNext('.b-load_more_comics_button');
        } else {
            more_button = button;
            list_element = more_button.getPrevious('.b-comics_list');
        }

        if (!options.load_more) {
            list_element.getElements('.post').destroy();
            list_element.getElements('.b-comics_list_page').destroy();
            more_button.addClass('hidden');
            list_element.addClass('js-loading_animation');

            loading_animation_attribute = 'opacity';
            loading_animation_color_to = 1;
            loading_animation_color_from = 1;
        } else if (options.list) {
            loading_animation_attribute = 'opacity';
            loading_animation_color_to = 0.5;
            loading_animation_color_from = 1;
        } else {
            loading_animation_attribute = 'background-color';
            loading_animation_color_to = '#005aaa';
            loading_animation_color_from = '#898989';
        }

        data = 'offset=' + list_element.getElements('.post').length;

        if (options.sort) {
            url += options.sort;
        }

        new futuAjax({
            button: $(button),
            attribute: loading_animation_attribute,
            color_to: loading_animation_color_to,
            color_from: loading_animation_color_from,
            url: url,
            data: data,
            onLoadFunction: function (response) {
                $(list_element).removeClass('js-loading_animation');

                var list_page = new Element('div', {
                    html: response.template,
                    'class': 'b-comics_list_page'
                });
                list_page.getElements('.post').removeClass('pos_hidden');
                list_page.inject(list_element);


                if (response.offset) {
                    more_button.removeClass('hidden');
                } else {
                    more_button.addClass('hidden');
                }

                datesHandler.setDates();
            }
        });
    },
    setTimer: function (contest_ends) {
        var contest_timer_element = $('js-promo_page_comics_timer');
        var current_date = (new Date()).getTime();

        var till_contest_ends = contest_ends * 1000 - current_date;

        var timer = till_contest_ends;

        if (timer < 0) {
            timer = 0;
        }
        if (timer >= 0) {
            var x = timer / 1000;
            var timer_seconds = Math.floor(x % 60);
            if (timer_seconds < 10) {
                timer_seconds = '0' + timer_seconds;
            }
            x /= 60;
            var timer_minutes = Math.floor(x % 60);
            if (timer_minutes < 10) {
                timer_minutes = '0' + timer_minutes;
            }
            x /= 60;
            var timer_hours = Math.floor(x % 24);
            if (timer_hours < 10) {
                timer_hours = '0' + timer_hours;
            }
            x /= 24;
            var timer_days = Math.floor(x);
            if (timer_days < 10) {
                timer_days = '0' + timer_days;
            }

            contest_timer_element.innerHTML = '<em>{timer_days}<i>д</i></em> <em>{timer_hours}<i>ч</i></em> <em>{timer_minutes}<i>м</i></em>'.substitute({
                timer_days: timer_days,
                timer_hours: timer_hours,
                timer_minutes: timer_minutes
            });

            democracyComicsHandler.setTimer.delay(60000, this, arguments);
        }
    }
};

datesHandler = {
    today: null,
    yesterday: null,
    tomorrow: null,

    setCurrentDays: function () {
        if (!this.today) {
            var todayTime;
            this.today = new Date().zeroTime();
            todayTime = this.today.getTime();
            this.yesterday = new Date(todayTime - 24 * 60 * 60 * 1000);
            this.tomorrow = new Date(todayTime + 24 * 60 * 60 * 1000);
        }
    },

    fancyDateFormat: function (time_s) {
        this.setCurrentDays();
        var date = new Date(time_s),
            dateZeroTime = new Date(time_s).zeroTime(),
            output = '';

        if (dateZeroTime.getTime() == this.today.getTime()) {
            output = 'сегодня';
        } else if (dateZeroTime.getTime() == this.yesterday.getTime()) {
            output = 'вчера';
        } else if (dateZeroTime.getTime() == this.tomorrow.getTime()) {
            output = 'завтра';
        } else {
            output = date.format('d mmmm yyyy');
        }
        output += ' в&nbsp;' + date.format('HH.MM');

        return output;
    },

    getTimeTillEvent: function (time_s) {
        this.setCurrentDays();

        var today = this.today,
            time = new Date(time_s).zeroTime();

        var timeDiff = time.getTime() - today.getTime(),
            daysDiff = Math.floor(timeDiff / (24 * 60 * 60 * 1000)),
            restTime = '';

        if (daysDiff > 1) {
            restTime = 'через ' + daysDiff + ' ' + utils.getPlural(daysDiff, ['день', 'дня', 'дней']);
        } else if (daysDiff > 0) {
            restTime = 'завтра';
        } else if (daysDiff == 0) {
            restTime = 'сегодня';
        } else {
            restTime = time.format('dd.mm.yyyy');
        }
        return restTime;
    },

    getTimeAmount: function (time1_s, time2_s) {
        var timeDiff = Math.abs(time1_s - time2_s),
            daysDiff = Math.floor(timeDiff / (24 * 60 * 60)),
            hoursDiff = Math.floor(timeDiff / (60 * 60)) - daysDiff * 24,
            minutesDiff = Math.floor(timeDiff / (60)) - daysDiff * 24 * 60 - hoursDiff * 60,
            secondsDiff = Math.floor(timeDiff) - daysDiff * 24 * 60 * 60 - hoursDiff * 60 * 60,
            timeAmount = '';

        if (daysDiff > 0) {
            if (daysDiff > 1) {
                timeAmount = daysDiff + ' ' + utils.getPlural(daysDiff, ['день', 'дня', 'дней']) + ' ';
            } else {
                timeAmount = 'день';
            }
        } else if (hoursDiff > 0) {
            if (hoursDiff > 1) {
                timeAmount = hoursDiff + ' ' + utils.getPlural(hoursDiff, ['час', 'часа', 'часов']) + ' ';
            } else {
                timeAmount = 'час';
            }
        } else if (minutesDiff > 0) {
            if (minutesDiff > 1) {
                timeAmount = minutesDiff + ' ' + utils.getPlural(minutesDiff, ['минуту', 'минуты', 'минут']) + ' ';
            } else {
                timeAmount = 'минуту';
            }
        } else if (secondsDiff > 0) {
            if (secondsDiff > 1) {
                timeAmount = secondsDiff + ' ' + utils.getPlural(secondsDiff, ['секунду', 'секунд', 'секунд']) + ' ';
            } else {
                timeAmount = 'секунду';
            }
        }
        return timeAmount;
    },

    setDates: function (dates_holder_element) {
        var dates_element = dates_holder_element ? dates_holder_element.getElements('.js-date:not(js-date__formatted)') : $$('.js-date:not(js-date__formatted)');
        dates_element.each(function (date_element) {
            var epoch = date_element.getAttribute('data-epoch_date');
            if (epoch && epoch > 0) {
                if (date_element.hasClass('js-date-regular')) {
                    date_element.innerHTML = (new Date(epoch * 1000).format('dd.mm.yyyy'));
                } else if (date_element.hasClass('js-date-regular-time')) {
                    date_element.innerHTML = (new Date(epoch * 1000).format('HH:MM'));
                } else if (date_element.hasClass('js-date-regular-date-time')) {
                    date_element.innerHTML = (new Date(epoch * 1000).format('d mmmm yyyy HH:MM'));
                } else if (date_element.hasClass('js-date-regular-num-date-time')) {
                    date_element.innerHTML = (new Date(epoch * 1000).format('d.mm.yyyy HH:MM'));
                } else if (date_element.hasClass('js-date-regular-dm')) {
                    date_element.innerHTML = (new Date(epoch * 1000).format('d mmmm'));
                } else if (date_element.hasClass('js-date-regular-date')) {
                    date_element.innerHTML = (new Date(epoch * 1000).format('d mmmm yyyy'));
                } else if (date_element.hasClass('js-date_in_time')) {
                    // Форматирование даты в формате: через какое время произойдет событие
                    date_element.innerHTML = this.getTimeTillEvent(epoch * 1000);
                } else {
                    date_element.innerHTML = this.fancyDateFormat(epoch * 1000);
                }
            }

            date_element.addClass('js-date__formatted');
        }.bind(this));
    }
};

/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal/>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */

var dateFormat = function () {
    var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|"[^"]*"|'[^']*'/g,
        timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
        timezoneClip = /[^-+\dA-Z]/g,
        pad = function (val, len) {
            val = String(val);
            len = len || 2;
            while (val.length < len) val = "0" + val;
            return val;
        };

    // Regexes and supporting functions are cached through closure
    return function (date, mask, utc) {
        var dF = dateFormat;

        // You can't provide utc if you skip other args (use the "UTC:" mask prefix)
        if (arguments.length == 1 && Object.prototype.toString.call(date) == "[object String]" && !/\d/.test(date)) {
            mask = date;
            date = undefined;
        }

        // Passing date through Date applies Date.parse, if necessary
        date = date ? new Date(date) : new Date;
        if (isNaN(date)) throw SyntaxError("invalid date");

        mask = String(dF.masks[mask] || mask || dF.masks["default"]);

        // Allow setting the utc argument via the mask
        if (mask.slice(0, 4) == "UTC:") {
            mask = mask.slice(4);
            utc = true;
        }

        var _ = utc ? "getUTC" : "get",
            d = date[_ + "Date"](),
            D = date[_ + "Day"](),
            m = date[_ + "Month"](),
            y = date[_ + "FullYear"](),
            H = date[_ + "Hours"](),
            M = date[_ + "Minutes"](),
            s = date[_ + "Seconds"](),
            L = date[_ + "Milliseconds"](),
            o = utc ? 0 : date.getTimezoneOffset(),
            flags = {
                d: d,
                dd: pad(d),
                ddd: dF.i18n.dayNames[D],
                dddd: dF.i18n.dayNames[D + 7],
                m: m + 1,
                mm: pad(m + 1),
                mmm: dF.i18n.monthNames[m],
                mmmm: dF.i18n.monthNames[m + 12],
                yy: String(y).slice(2),
                yyyy: y,
                h: H % 12 || 12,
                hh: pad(H % 12 || 12),
                H: H,
                HH: pad(H),
                M: M,
                MM: pad(M),
                s: s,
                ss: pad(s),
                l: pad(L, 3),
                L: pad(L > 99 ? Math.round(L / 10) : L),
                t: H < 12 ? "a" : "p",
                tt: H < 12 ? "am" : "pm",
                T: H < 12 ? "A" : "P",
                TT: H < 12 ? "AM" : "PM",
                Z: utc ? "UTC" : (String(date).match(timezone) || [""]).pop().replace(timezoneClip, ""),
                o: (o > 0 ? "-" : "+") + pad(Math.floor(Math.abs(o) / 60) * 100 + Math.abs(o) % 60, 4),
                S: ["th", "st", "nd", "rd"][d % 10 > 3 ? 0 : (d % 100 - d % 10 != 10) * d % 10]
            };

        return mask.replace(token, function ($0) {
            return $0 in flags ? flags[$0] : $0.slice(1, $0.length - 1);
        });
    };
}();

// Some common format strings
dateFormat.masks = {
    "default": "ddd mmm dd yyyy HH:MM:ss",
    shortDate: "m/d/yy",
    mediumDate: "mmm d, yyyy",
    longDate: "mmmm d, yyyy",
    fullDate: "dddd, mmmm d, yyyy",
    shortTime: "h:MM TT",
    mediumTime: "h:MM:ss TT",
    longTime: "h:MM:ss TT Z",
    isoDate: "yyyy-mm-dd",
    isoTime: "HH:MM:ss",
    isoDateTime: "yyyy-mm-dd'T'HH:MM:ss",
    isoUtcDateTime: "UTC:yyyy-mm-dd'T'HH:MM:ss'Z'"
};

// Internationalization strings
dateFormat.i18n = {
    dayNames: [
        "Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб",
        "Воскресенье", "Поенедельник", "Вторник", "Среда", "Четверг", "Пятница", "Суббота"
    ],
    monthNames: [
        "Янв", "Фев", "Март", "Апр", "Май", "Июнь", "Июль", "Авг", "Сен", "Окт", "Ноя", "Дек",
        "Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря"
    ]
};

// For convenience...
Date.prototype.format = function (mask, utc) {
    return dateFormat(this, mask, utc);
};

/*
 * Date prototype extensions. Doesn't depend on any
 * other code. Doens't overwrite existing methods.
 *
 * Adds dayNames, abbrDayNames, monthNames and abbrMonthNames static properties and isLeapYear,
 * isWeekend, isWeekDay, getDaysInMonth, getDayName, getMonthName, getDayOfYear, getWeekOfYear,
 * setDayOfYear, addYears, addMonths, addDays, addHours, addMinutes, addSeconds methods
 *
 * Copyright (c) 2006 Jörn Zaefferer and Brandon Aaron (brandon.aaron@gmail.com || http://brandonaaron.net)
 *
 * Additional methods and properties added by Kelvin Luck: firstDayOfWeek, dateFormat, zeroTime, asString, fromString -
 * I've added my name to these methods so you know who to blame if they are broken!
 * 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * An Array of day names starting with Sunday.
 *
 * @example dayNames[0]
 * @result 'Sunday'
 *
 * @name dayNames
 * @type Array
 * @cat Plugins/Methods/Date
 */
Date.dayNames = ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];

/**
 * An Array of abbreviated day names starting with Sun.
 *
 * @example abbrDayNames[0]
 * @result 'Sun'
 *
 * @name abbrDayNames
 * @type Array
 * @cat Plugins/Methods/Date
 */
Date.abbrDayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
Date.classDayNames = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];

/**
 * An Array of month names starting with Janurary.
 *
 * @example monthNames[0]
 * @result 'January'
 *
 * @name monthNames
 * @type Array
 * @cat Plugins/Methods/Date
 */
Date.monthNames = ['Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря'];

/**
 * An Array of abbreviated month names starting with Jan.
 *
 * @example abbrMonthNames[0]
 * @result 'Jan'
 *
 * @name monthNames
 * @type Array
 * @cat Plugins/Methods/Date
 */
Date.abbrMonthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

/**
 * The first day of the week for this locale.
 *
 * @name firstDayOfWeek
 * @type Number
 * @cat Plugins/Methods/Date
 * @author Kelvin Luck
 */
Date.firstDayOfWeek = 1;

/**
 * The format that string dates should be represented as (e.g. 'dd/mm/yyyy' for UK, 'mm/dd/yyyy' for US, 'yyyy-mm-dd' for Unicode etc).
 *
 * @name format
 * @type String
 * @cat Plugins/Methods/Date
 * @author Kelvin Luck
 */
Date.format = 'dd/mm/yyyy';
//Date.format = 'mm/dd/yyyy';
//Date.format = 'yyyy-mm-dd';
//Date.format = 'dd mmm yy';

/**
 * The first two numbers in the century to be used when decoding a two digit year. Since a two digit year is ambiguous (and date.setYear
 * only works with numbers < 99 and so doesn't allow you to set years after 2000) we need to use this to disambiguate the two digit year codes.
 *
 * @name format
 * @type String
 * @cat Plugins/Methods/Date
 * @author Kelvin Luck
 */
Date.fullYearStart = '20';

(function () {

    /**
     * Adds a given method under the given name
     * to the Date prototype if it doesn't
     * currently exist.
     *
     * @private
     */
    function add(name, method) {
        if (!Date.prototype[name]) {
            Date.prototype[name] = method;
        }
    };

    /**
     * Checks if the year is a leap year.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.isLeapYear();
     * @result true
     *
     * @name isLeapYear
     * @type Boolean
     * @cat Plugins/Methods/Date
     */
    add("isLeapYear", function () {
        var y = this.getFullYear();
        return (y % 4 == 0 && y % 100 != 0) || y % 400 == 0;
    });

    /**
     * Checks if the day is a weekend day (Sat or Sun).
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.isWeekend();
     * @result false
     *
     * @name isWeekend
     * @type Boolean
     * @cat Plugins/Methods/Date
     */
    add("isWeekend", function () {
        return this.getDay() == 0 || this.getDay() == 6;
    });

    /**
     * Check if the day is a day of the week (Mon-Fri)
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.isWeekDay();
     * @result false
     *
     * @name isWeekDay
     * @type Boolean
     * @cat Plugins/Methods/Date
     */
    add("isWeekDay", function () {
        return !this.isWeekend();
    });

    /**
     * Gets the number of days in the month.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.getDaysInMonth();
     * @result 31
     *
     * @name getDaysInMonth
     * @type Number
     * @cat Plugins/Methods/Date
     */
    add("getDaysInMonth", function () {
        return [31, (this.isLeapYear() ? 29 : 28), 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][this.getMonth()];
    });

    /**
     * Gets the name of the day.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.getDayName();
     * @result 'Saturday'
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.getDayName(true);
     * @result 'Sat'
     *
     * @param abbreviated Boolean When set to true the name will be abbreviated.
     * @name getDayName
     * @type String
     * @cat Plugins/Methods/Date
     */
    add("getDayName", function (abbreviated) {
        return abbreviated ? Date.abbrDayNames[this.getDay()] : Date.dayNames[this.getDay()];
    });

    /**
     * Gets the name of the month.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.getMonthName();
     * @result 'Janurary'
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.getMonthName(true);
     * @result 'Jan'
     *
     * @param abbreviated Boolean When set to true the name will be abbreviated.
     * @name getDayName
     * @type String
     * @cat Plugins/Methods/Date
     */
    add("getMonthName", function (abbreviated) {
        return abbreviated ? Date.abbrMonthNames[this.getMonth()] : Date.monthNames[this.getMonth()];
    });

    /**
     * Get the number of the day of the year.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.getDayOfYear();
     * @result 11
     *
     * @name getDayOfYear
     * @type Number
     * @cat Plugins/Methods/Date
     */
    add("getDayOfYear", function () {
        var tmpdtm = new Date("1/1/" + this.getFullYear());
        return Math.floor((this.getTime() - tmpdtm.getTime()) / 86400000);
    });

    /**
     * Get the number of the week of the year.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.getWeekOfYear();
     * @result 2
     *
     * @name getWeekOfYear
     * @type Number
     * @cat Plugins/Methods/Date
     */
    add("getWeekOfYear", function () {
        return Math.ceil(this.getDayOfYear() / 7);
    });

    /**
     * Set the day of the year.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.setDayOfYear(1);
     * dtm.toString();
     * @result 'Tue Jan 01 2008 00:00:00'
     *
     * @name setDayOfYear
     * @type Date
     * @cat Plugins/Methods/Date
     */
    add("setDayOfYear", function (day) {
        this.setMonth(0);
        this.setDate(day);
        return this;
    });

    /**
     * Add a number of years to the date object.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.addYears(1);
     * dtm.toString();
     * @result 'Mon Jan 12 2009 00:00:00'
     *
     * @name addYears
     * @type Date
     * @cat Plugins/Methods/Date
     */
    add("addYears", function (num) {
        this.setFullYear(this.getFullYear() + num);
        return this;
    });

    /**
     * Add a number of months to the date object.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.addMonths(1);
     * dtm.toString();
     * @result 'Tue Feb 12 2008 00:00:00'
     *
     * @name addMonths
     * @type Date
     * @cat Plugins/Methods/Date
     */
    add("addMonths", function (num) {
        var tmpdtm = this.getDate();

        this.setMonth(this.getMonth() + num);

        if (tmpdtm > this.getDate())
            this.addDays(-this.getDate());

        return this;
    });

    /**
     * Add a number of days to the date object.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.addDays(1);
     * dtm.toString();
     * @result 'Sun Jan 13 2008 00:00:00'
     *
     * @name addDays
     * @type Date
     * @cat Plugins/Methods/Date
     */
    add("addDays", function (num) {
        //this.setDate(this.getDate() + num);
        this.setTime(this.getTime() + (num * 86400000));
        return this;
    });

    /**
     * Add a number of hours to the date object.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.addHours(24);
     * dtm.toString();
     * @result 'Sun Jan 13 2008 00:00:00'
     *
     * @name addHours
     * @type Date
     * @cat Plugins/Methods/Date
     */
    add("addHours", function (num) {
        this.setHours(this.getHours() + num);
        return this;
    });

    /**
     * Add a number of minutes to the date object.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.addMinutes(60);
     * dtm.toString();
     * @result 'Sat Jan 12 2008 01:00:00'
     *
     * @name addMinutes
     * @type Date
     * @cat Plugins/Methods/Date
     */
    add("addMinutes", function (num) {
        this.setMinutes(this.getMinutes() + num);
        return this;
    });

    /**
     * Add a number of seconds to the date object.
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.addSeconds(60);
     * dtm.toString();
     * @result 'Sat Jan 12 2008 00:01:00'
     *
     * @name addSeconds
     * @type Date
     * @cat Plugins/Methods/Date
     */
    add("addSeconds", function (num) {
        this.setSeconds(this.getSeconds() + num);
        return this;
    });

    /**
     * Sets the time component of this Date to zero for cleaner, easier comparison of dates where time is not relevant.
     *
     * @example var dtm = new Date();
     * dtm.zeroTime();
     * dtm.toString();
     * @result 'Sat Jan 12 2008 00:01:00'
     *
     * @name zeroTime
     * @type Date
     * @cat Plugins/Methods/Date
     * @author Kelvin Luck
     */
    add("zeroTime", function () {
        this.setMilliseconds(0);
        this.setSeconds(0);
        this.setMinutes(0);
        this.setHours(0);
        return this;
    });

    /**
     * Returns a string representation of the date object according to Date.format.
     * (Date.toString may be used in other places so I purposefully didn't overwrite it)
     *
     * @example var dtm = new Date("01/12/2008");
     * dtm.asString();
     * @result '12/01/2008' // (where Date.format == 'dd/mm/yyyy'
     *
     * @name asString
     * @type Date
     * @cat Plugins/Methods/Date
     * @author Kelvin Luck
     */
    add("asString", function (format) {
        var r = format || Date.format;
        return r
            .split('yyyy').join(this.getFullYear())
            .split('yy').join((this.getFullYear() + '').substring(2))
            .split('mmmm').join(this.getMonthName(false))
            .split('mmm').join(this.getMonthName(true))
            .split('mm').join(_zeroPad(this.getMonth() + 1))
            .split('dd').join(_zeroPad(this.getDate()))
            .split('hh').join(_zeroPad(this.getHours()))
            .split('min').join(_zeroPad(this.getMinutes()))
            .split('ss').join(_zeroPad(this.getSeconds()));
    });

    /**
     * Returns a new date object created from the passed String according to Date.format or false if the attempt to do this results in an invalid date object
     * (We can't simple use Date.parse as it's not aware of locale and I chose not to overwrite it incase it's functionality is being relied on elsewhere)
     *
     * @example var dtm = Date.fromString("12/01/2008");
     * dtm.toString();
     * @result 'Sat Jan 12 2008 00:00:00' // (where Date.format == 'dd/mm/yyyy'
     *
     * @name fromString
     * @type Date
     * @cat Plugins/Methods/Date
     * @author Kelvin Luck
     */
    Date.fromString = function (s, format) {
        var f = format || Date.format;
        var d = new Date('01/01/1977');

        var mLength = 0;

        var iM = f.indexOf('mmmm');
        if (iM > -1) {
            for (var i = 0; i < Date.monthNames.length; i++) {
                var mStr = s.substr(iM, Date.monthNames[i].length);
                if (Date.monthNames[i] == mStr) {
                    mLength = Date.monthNames[i].length - 4;
                    break;
                }
            }
            d.setMonth(i);
        } else {
            iM = f.indexOf('mmm');
            if (iM > -1) {
                var mStr = s.substr(iM, 3);
                for (var i = 0; i < Date.abbrMonthNames.length; i++) {
                    if (Date.abbrMonthNames[i] == mStr) break;
                }
                d.setMonth(i);
            } else {
                d.setMonth(Number(s.substr(f.indexOf('mm'), 2)) - 1);
            }
        }

        var iY = f.indexOf('yyyy');

        if (iY > -1) {
            if (iM < iY) {
                iY += mLength;
            }
            d.setFullYear(Number(s.substr(iY, 4)));
        } else {
            if (iM < iY) {
                iY += mLength;
            }
            // TODO - this doesn't work very well - are there any rules for what is meant by a two digit year?
            d.setFullYear(Number(Date.fullYearStart + s.substr(f.indexOf('yy'), 2)));
        }
        var iD = f.indexOf('dd');
        if (iM < iD) {
            iD += mLength;
        }
        d.setDate(Number(s.substr(iD, 2)));
        if (isNaN(d.getTime())) {
            return false;
        }
        return d;
    };

    // utility method
    var _zeroPad = function (num) {
        var s = '0' + num;
        return s.substring(s.length - 2)
        //return ('0'+num).substring(-2); // doesn't work on IE :(
    };

})();
audioHandler = {
    init: function () {
        var new_audio_elements = [];
        $$('audio').each(function (audio_element) {
            if (!audio_element.getParent('.audiojs')) {
                new_audio_elements.push(audio_element);
            }
        });
        var audio_instances = audiojs.createAll({}, new_audio_elements);
    }
};

/**
 * ReplaceAll by Fagner Brack (MIT Licensed)
 * Replaces all occurrences of a substring in a string
 */
String.prototype.replaceAll = function (token, newToken, ignoreCase) {
    var _token;
    var str = this + "";
    var i = -1;

    if (typeof token === "string") {

        if (ignoreCase) {

            _token = token.toLowerCase();

            while ((
                i = str.toLowerCase().indexOf(
                    token, i >= 0 ? i + newToken.length : 0
                ) ) !== -1
                ) {
                str = str.substring(0, i) +
                    newToken +
                    str.substring(i + token.length);
            }

        } else {
            return this.split(token).join(newToken);
        }

    }
    return str;
};

tipsHandler = {
    initTips: function () {
        if (!utils.isMobileDevice) {
            new Tips('.b-fui_icon_button_get_a_life, .b-header_nav .b-fui_icon_button_interest, .b-header_nav .b-fui_icon_button_favourites, .b-header_nav .b-fui_icon_button_inbox, .b-list_item_domain_status, .b-ranks_current_rank_text, .b-top_panel_user_menu_karma', {
                showDelay: 0,
                hideDelay: 200,
                offset: {
                    x: function (tip, hovered) {
                        return 0;
                    },
                    y: function (tip, hovered) {
                        var hovered_coords = hovered.getCoordinates();
                        return hovered_coords.height + 6
                    }
                },
                title: function (hovered) {
                    var html = '<i class="b-tip_arrow_container"><i class="b-tip_arrow"></i></i>';

                    return html + hovered.title;
                },
                windowPadding: {
                    x: -100,
                    y: -100
                },
                fixed: true
            });
        }
    }
};
iconsHandler = {
    spriteUrl: '/static/i/icons_sprite.png',
    icons: {
        new_post: {
            svg: '<svg version="1.2" baseProfile="tiny" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" overflow="inherit" xml:space="preserve"><rect x="7" y="9" width="5" height="1"/><rect x="7" y="11" width="5" height="1"/><rect x="7" y="13" width="4" height="1"/><path d="M13,11v4H6V8h4V6H4.5605469C4.2509766,6,4,6.2509766,4,6.5605469v9.8789062C4,16.7490234,4.2509766,17,4.5605469,17h9.8789062C14.7490234,17,15,16.7490234,15,16.4394531V11H13z"/><polygon points="17,6 15,6 15,4 13,4 13,6 11,6 11,8 13,8 13,10 15,10 15,8 17,8 "/></svg>',
            code: 'H'
        },
        settings: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><g id="Layer_3"><path fill="#010101" d="M15.9511719,13.0058594l-1.203125,2.0488281c-0.5585938-0.3125-1.1464844-0.3867188-1.7636719-0.2246094s-1.0859375,0.5117188-1.4013672,1.0527344c-0.2050781,0.3496094-0.3056641,0.7324219-0.3056641,1.1464844H8.8735352c0-0.640625-0.2285156-1.1816406-0.6875-1.6279297c-0.4560547-0.4453125-1.0039062-0.6660156-1.6386719-0.6660156c-0.4199219,0-0.8085938,0.1054688-1.1660156,0.3183594l-1.2001953-2.0478516c0.5585938-0.3271484,0.921875-0.7919922,1.0869141-1.4013672c0.1660156-0.6064453,0.0888672-1.1865234-0.2294922-1.737793c-0.2021484-0.3505859-0.4882812-0.625-0.8574219-0.8251953l1.2001953-2.0498047c0.5605469,0.3125,1.1533203,0.3886719,1.7753906,0.2265625c0.625-0.1621094,1.0947266-0.5214844,1.4121094-1.0722656c0.203125-0.3496094,0.3046875-0.7324219,0.3046875-1.1464844h2.4038086c0,0.6386719,0.2265625,1.1816406,0.6777344,1.625c0.453125,0.4453125,1.0019531,0.6660156,1.6484375,0.6660156c0.4199219,0,0.8085938-0.0976562,1.1660156-0.2988281L15.96875,9.0405273c-0.5585938,0.3125-0.9238281,0.7773438-1.0957031,1.3911133c-0.171875,0.6132812-0.09375,1.1914062,0.2402344,1.7285156C15.3164062,12.5234375,15.59375,12.8046875,15.9511719,13.0058594z M12.5546875,11.0136719c0-0.6757812-0.2402344-1.2524414-0.7246094-1.7290039c-0.4824219-0.4765625-1.0683594-0.7148438-1.7539062-0.7148438c-0.6879883,0-1.2709961,0.2382812-1.7563477,0.7148438c-0.4833984,0.4765625-0.7246094,1.0532227-0.7246094,1.7290039c0,0.6748047,0.2412109,1.2529297,0.7246094,1.7275391c0.4853516,0.4785156,1.0683594,0.7148438,1.7553711,0.7158203c0.6865234,0,1.2714844-0.2363281,1.7548828-0.7148438C12.3134766,12.2675781,12.5546875,11.6894531,12.5546875,11.0136719z"/></g></svg>',
            code: '&amp;'
        },
        cases_empty: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><g id="Layer_3"><g><path opacity="0.3" fill="#010101" enable-background="new    " d="M8,7h5.4394531C13.7490234,7,14,7.2509766,14,7.5605469V13h1V6H8V7z"/><rect x="14" y="13" fill="#010101" width="1" height="0.375"/><g><path fill="#010101" d="M16.4394531,4H6.5605469C6.2509766,4,6,4.2509766,6,4.5605469V7H4.5605469C4.2509766,7,4,7.2509766,4,7.5605469v8.8789062C4,16.7490234,4.2509766,17,4.5605469,17h8.8789062C13.7490234,17,14,16.7490234,14,16.4394531V15h2.4394531C16.7490234,15,17,14.7490234,17,14.4394531V4.5605469C17,4.2509766,16.7490234,4,16.4394531,4z M13,15H6V8h7V15z M15,13v0.375h-1V13V7.5605469C14,7.2509766,13.7490234,7,13.4394531,7H8V6h7V13z"/><rect x="7" y="9" fill="#010101" width="5" height="1"/><rect x="7" y="11" fill="#010101" width="5" height="1"/><rect x="7" y="13" fill="#010101" width="4" height="1"/></g></g></g></svg>',
            code: 'P'
        },
        cases_full: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><g id="Layer_3"><g><path opacity="0.3" fill="#010101" enable-background="new    " d="M6,15h7V8H6V15z M7,9h1h4v1H8H7V9z M7,11h1h4v1H8H7V11z M7,13h1h3v0.375V14H7V13z"/><path opacity="0.3" fill="#010101" enable-background="new    " d="M8,7h5.4394531C13.7490234,7,14,7.2509766,14,7.5605469V13h1V6H8V7z"/><rect x="14" y="13" fill="#010101" width="1" height="0.375"/><g><path fill="#010101" d="M16.4394531,4H6.5605469C6.2509766,4,6,4.2509766,6,4.5605469V7H4.5605469C4.2509766,7,4,7.2509766,4,7.5605469v8.8789062C4,16.7490234,4.2509766,17,4.5605469,17h8.8789062C13.7490234,17,14,16.7490234,14,16.4394531V15h2.4394531C16.7490234,15,17,14.7490234,17,14.4394531V4.5605469C17,4.2509766,16.7490234,4,16.4394531,4z M13,15H6V8h7V15z M15,13v0.375h-1V13V7.5605469C14,7.2509766,13.7490234,7,13.4394531,7H8V6h7V13z"/><rect x="7" y="9" fill="#010101" width="5" height="1"/><rect x="7" y="11" fill="#010101" width="5" height="1"/><rect x="7" y="13" fill="#010101" width="4" height="1"/></g></g></g></svg>',
            code: 'O'
        },
        envelope_empty: {
            svg: '<svg version="1.2" baseProfile="tiny" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" overflow="inherit" xml:space="preserve"><path d="M15.4326172,7H4.5664062C4.2539062,7,4,7.2597656,4,7.5810547v8.8369141C4,16.7402344,4.2539062,17,4.5664062,17h10.8662109C15.7460938,17,16,16.7402344,16,16.4179688V7.5810547C16,7.2597656,15.7460938,7,15.4326172,7z M10.0253906,12L6.0087891,8H14L10.0253906,12z M6,15V9.0004883L10.0253906,13L14,9.0004883V15H6z"/></svg>',
            code: 'S'
        },
        envelope_full: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><rect x="10.0039062" y="4.0048828" fill="none" width="0.0009766" height="7.9912109"/><polygon opacity="0.3" enable-background="new    " points="10.0253906,12 14,8.0004883 6.0092773,8.0004883 "/><polygon opacity="0.3" enable-background="new    " points="6,9.0004883 6,15 14,15 14,9.0004883 10.0253906,13 "/><path d="M15.4326172,7H4.5664062C4.2539062,7,4,7.2597656,4,7.5810547v8.8369141C4,16.7402344,4.2539062,17,4.5664062,17h10.8662109C15.7460938,17,16,16.7402344,16,16.4179688V7.5810547C16,7.2597656,15.7460938,7,15.4326172,7z M14,15H6V9.0004883L10.0253906,13L14,9.0004883V15z M14,8.0004883L10.0253906,12L6.0092773,8.0004883L6.0087891,8H14V8.0004883z"/></svg>',
            code: 'S'
        },
        stars_empty: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><g><path d="M16.5576172,9.4995117c-0.0869141-0.2666016-0.3173828-0.4619141-0.5947266-0.5009766l-2.7880859-0.4072266l-0.6699219-0.0976562l-0.4482422-0.9082031l-1.0986328-2.2255859c-0.2490234-0.5029297-1.0732422-0.5029297-1.3222656,0L8.7124023,7.230957L8.0888672,8.4936523L6.7441406,8.6899414L4.6308594,8.9985352c-0.2773438,0.0390625-0.5078125,0.234375-0.5947266,0.5009766c-0.0869141,0.265625-0.0146484,0.5585938,0.1855469,0.7548828l1.9746094,1.9233398l0.5283203,0.5136719L6.644043,13.1640625l-0.5083008,2.9746094c-0.0488281,0.2753906,0.0664062,0.5546875,0.2929688,0.71875c0.2255859,0.1640625,0.5263672,0.1894531,0.7753906,0.0566406l3.0927734-1.625l3.0927734,1.625c0.1074219,0.0585938,0.2255859,0.0839844,0.34375,0.0839844c0.1513672,0,0.3037109-0.046875,0.4316406-0.140625c0.2265625-0.1640625,0.3417969-0.4433594,0.2929688-0.71875l-0.5888672-3.4472656l2.5029297-2.4370117C16.5722656,10.0581055,16.6445312,9.7651367,16.5576172,9.4995117z M9.3095703,10.0708008L10.296875,8.074707l0.9873047,1.9960938l2.2021484,0.3203125l-1.59375,1.5541992l0.375,2.1953125l-1.9707031-1.0371094L8.3261719,14.140625l0.375-2.1953125l-1.59375-1.5541992L9.3095703,10.0708008z"/></g></svg>',
            code: 'L'
        },
        stars_full: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><g><polygon opacity="0.3" enable-background="new    " points="8.3261719,14.140625 10.296875,13.1035156 12.2675781,14.140625 11.8925781,11.9453125 13.4863281,10.3911133 11.2841797,10.0708008 10.296875,8.074707 9.3095703,10.0708008 7.1074219,10.3911133 8.7011719,11.9453125 	"/><path d="M16.5576172,9.4995117c-0.0869141-0.2666016-0.3173828-0.4619141-0.5947266-0.5009766l-2.7880859-0.4072266l-0.6699219-0.0976562l-0.4482422-0.9082031l-1.0986328-2.2255859c-0.2490234-0.5029297-1.0732422-0.5029297-1.3222656,0L8.7124023,7.230957L8.0888672,8.4936523L6.7441406,8.6899414L4.6308594,8.9985352c-0.2773438,0.0390625-0.5078125,0.234375-0.5947266,0.5009766c-0.0869141,0.265625-0.0146484,0.5585938,0.1855469,0.7548828l1.9746094,1.9233398l0.5283203,0.5136719L6.644043,13.1640625l-0.5083008,2.9746094c-0.0488281,0.2753906,0.0664062,0.5546875,0.2929688,0.71875c0.2255859,0.1640625,0.5263672,0.1894531,0.7753906,0.0566406l3.0927734-1.625l3.0927734,1.625c0.1074219,0.0585938,0.2255859,0.0839844,0.34375,0.0839844c0.1513672,0,0.3037109-0.046875,0.4316406-0.140625c0.2265625-0.1640625,0.3417969-0.4433594,0.2929688-0.71875l-0.5888672-3.4472656l2.5029297-2.4370117C16.5722656,10.0581055,16.6445312,9.7651367,16.5576172,9.4995117z M9.3095703,10.0708008L10.296875,8.074707l0.9873047,1.9960938l2.2021484,0.3203125l-1.59375,1.5541992l0.375,2.1953125l-1.9707031-1.0371094L8.3261719,14.140625l0.375-2.1953125l-1.59375-1.5541992L9.3095703,10.0708008z"/></g></svg>',
            code: 'K'
        },
        magic: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><path fill="#010101" d="M12,9l5,5c0,0,0.5,0.4375,0,1c-0.3671875,0.4130859-1.6123047,1.6357422-2,2c-0.53125,0.5-1,0-1,0l-5-5V9H12z"/><polygon fill="#010101" points="10,6 8,6 8,4 6,4 6,6 4,6 4,8 6,8 6,10 8,10 8,8 10,8 "/></svg>',
            code: 'q'
        },
        find: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><path d="M12,9l5,5c0,0,0.5,0.4375,0,1c-0.3671875,0.4130859-1.6123047,1.6357422-2,2c-0.53125,0.5-1,0-1,0l-5-5l1.53125-1.5L12,9z"/><path d="M7.6816406,2.0566406c1.5639648,0,2.902832,0.5341797,4.015625,1.6010742c1.109375,1.0688477,1.6679688,2.3588867,1.6679688,3.8706055c0,0.9174805-0.2304688,1.7802734-0.6894531,2.5869141l-2.4960938,2.3095703C9.3901367,12.8085938,8.5571289,13,7.6816406,13c-1.5639648,0-2.9013672-0.5332031-4.0141602-1.6015625C2.5541992,10.331543,2,9.0410156,2,7.5283203c0-1.5117188,0.5541992-2.8017578,1.6674805-3.8706055C4.7802734,2.5908203,6.1176758,2.0566406,7.6816406,2.0566406z M7.6611328,10.9101562c0.9707031,0,1.796875-0.3310547,2.4819336-0.9916992c0.6821289-0.6611328,1.0249023-1.4584961,1.0249023-2.3901367c0-0.9335938-0.3427734-1.7290039-1.0249023-2.3886719C9.4580078,4.4765625,8.6318359,4.1464844,7.6611328,4.1464844c-0.9726562,0-1.7998047,0.3300781-2.4833984,0.9931641c-0.6845703,0.659668-1.0263672,1.4550781-1.0263672,2.3886719c0,0.9316406,0.3417969,1.7290039,1.0263672,2.3901367C5.8613281,10.5791016,6.6884766,10.9101562,7.6611328,10.9101562z"/></svg>',
            code: '"'
        },
        steps: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><path d="M7.8710938,8.8579102C7.3134766,9.1132812,6.8027344,9.2724609,6.3359375,9.3369141c-0.465332,0.0639648-0.7163086,0.0683594-0.753418,0.0141602C4.8881836,7.8525391,4.559082,6.628418,4.5961914,5.6782227C4.6323242,4.7280273,5.152832,3.9516602,6.1591797,3.3481445c1.003418-0.5107422,1.753418-0.5205078,2.2470703-0.0268555c0.4926758,0.4936523,0.784668,1.2109375,0.8759766,2.1513672c0.0913086,0.9414062-0.0180664,1.7397461-0.328125,2.3979492C8.7885742,8.2729492,8.4282227,8.6020508,7.8710938,8.8579102z M6.4042969,12.0898438c-0.2739258-0.5283203-0.4106445-1.0224609-0.4106445-1.4794922l3.0146484-0.3833008L9.0625,10.6665039c0.1464844,1.4233398-0.2451172,2.1918945-1.1772461,2.3022461C7.2451172,13.0410156,6.7514648,12.7480469,6.4042969,12.0898438z M12.0566406,13.90625c0.5556641,0.2460938,1.0673828,0.4013672,1.5341797,0.4667969c0.4658203,0.0625,0.7070312,0.0683594,0.7255859,0.0126953c0.7128906-1.4794922,1.0507812-2.6953125,1.0146484-3.6450195c-0.0371094-0.9501953-0.5585938-1.7260742-1.5625-2.3300781c-1.0048828-0.5297852-1.7539062-0.5429688-2.2480469-0.0410156c-0.4931641,0.503418-0.7851562,1.2192383-0.8754883,2.1513672c-0.0922852,0.9321289,0.0083008,1.7270508,0.3012695,2.3852539C11.1279297,13.3261719,11.4980469,13.6591797,12.0566406,13.90625z M13.7138672,16.6328125l0.21875-0.9853516l-3.0136719-0.3583984c-0.0180664,0.0556641-0.0371094,0.1416016-0.0551758,0.2607422c-0.019043,0.1201172-0.027832,0.3291016-0.027832,0.6298828c0,0.3027344,0.027832,0.5673828,0.0830078,0.7949219c0.0541992,0.2304688,0.1767578,0.453125,0.3701172,0.671875c0.1904297,0.21875,0.4423828,0.3476562,0.7529297,0.3837891C12.8271484,18.1220703,13.3847656,17.65625,13.7138672,16.6328125z"/></svg>',
            code: '#'
        },
        down: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><path d="M13,6l2,2l-5,5L5,8l2-2l3,3L13,6z"/></svg>'
        },
        chick: {
            svg: '<svg version="1.1" id="Layer_1" xmlns:x="&ns_extend;" xmlns:i="&ns_ai;" xmlns:graph="&ns_graphs;" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:a="http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><g><circle fill="#231F20" cx="10.499" cy="3.856" r="0.441"/><path fill="#231F20" d="M17.655,11.498c2.782-0.986,2.335-2.916,2.335-2.916c-0.539,1.032-1.975,1.032-1.975,1.032c2.468-1.166,1.703-2.288,1.703-2.288c-1.165,1.167-4.081,1.481-4.812,1.717c-0.528,0.169-1.618,1.069-2.184,1.556c-0.133-0.178-0.231-0.233-0.231-0.233c0.336-0.437,0.296-1.131,0.296-1.131l0.789-0.351c-0.464-0.448-0.889-0.544-0.889-0.544c-0.066-0.235-0.338-0.552-0.338-0.552L8.799,7.84c-0.866,0.638-1.386,1.597-1.693,2.599C6.513,9.938,5.584,9.195,5.11,9.043c-0.732-0.236-3.649-0.55-4.815-1.717c0,0-0.763,1.122,1.704,2.288c0,0-1.434,0-1.973-1.032c0,0-0.449,1.929,2.332,2.916c0,0-0.717,0.359-1.974-0.719c0,0-0.269,1.616,1.974,2.379c0,0-0.269,0.269-0.941-0.045c0,0,0.851,2.468,4.935,1.795c0.142-0.024,0.275-0.052,0.402-0.08c0.104,0.646,0.325,1.378,0.73,2.187c0.341,0.684,1.145,2.149,1.453,2.202c0.163,0.026,0.354-0.49,0.5-0.984c0.003,0,0.006,0.002,0.006,0.002S10.131,20,10.322,20c0.175,0,0.647-1.616,0.737-1.916c0.173,0.54,0.387,1.234,0.472,1.133c1.281-1.503,1.744-3.075,1.937-4.365c0.04,0.009,0.156,0.048,0.198,0.057c4.08,0.673,4.934-1.795,4.934-1.795c-0.673,0.313-0.944,0.045-0.944,0.045c2.245-0.763,1.975-2.379,1.975-2.379C18.373,11.857,17.655,11.498,17.655,11.498z M10.834,8.576c0,0,0.239-0.566,0.541-0.44c0.451,0.189,0.65,0.271,0.65,0.271C11.733,9.173,10.834,8.576,10.834,8.576z"/><path fill="#231F20" d="M10.429,3.072c-0.026,0.191-0.154,0.286-0.154,0.286l0.441-0.001c0,0-0.127-0.094-0.154-0.285c0.191,0.025,0.285,0.151,0.285,0.151V2.783c0,0-0.095,0.128-0.285,0.155c0.024-0.192,0.151-0.287,0.151-0.287l-0.441,0.002c0,0,0.129,0.094,0.155,0.285c-0.19-0.025-0.287-0.153-0.287-0.153l0.002,0.443C10.143,3.228,10.237,3.099,10.429,3.072z M10.494,2.929c0.041,0,0.075,0.034,0.075,0.074c0,0.042-0.031,0.075-0.073,0.075c-0.042,0-0.076-0.032-0.076-0.073C10.42,2.963,10.453,2.929,10.494,2.929z"/><path fill="#231F20" d="M8.463,6.08c0.011,0.028,0.019,0.053,0.028,0.077c0.042,0.155,0.116,0.367,0.188,0.564L8.676,6.722l0.072,0.695l3.527-0.015l0.092-0.693l-0.021-0.021c0.104-0.299,0.212-0.628,0.213-0.733c0.055-0.134,0.124-0.275,0.196-0.376c0.126-0.172,0.188-0.386,0.188-0.604c0-0.16-0.037-0.326-0.127-0.471c-0.088-0.145-0.234-0.268-0.424-0.329c-0.087-0.027-0.173-0.04-0.258-0.04c-0.258,0.004-0.449,0.112-0.651,0.193c-0.188,0.077-0.391,0.149-0.674,0.158V4.392l-0.618,0.002l0.002,0.097C9.892,4.488,9.68,4.417,9.485,4.336c-0.204-0.08-0.396-0.185-0.654-0.188c-0.083,0-0.171,0.014-0.259,0.042C8.386,4.252,8.24,4.375,8.151,4.522C8.063,4.669,8.027,4.834,8.029,4.993C8.03,5.21,8.094,5.424,8.22,5.597C8.316,5.727,8.403,5.921,8.463,6.08z M8.989,6.437L8.848,6.564C8.834,6.524,8.82,6.483,8.807,6.445c0.048-0.014,0.11-0.029,0.187-0.042C8.993,6.415,8.991,6.426,8.989,6.437z M12.171,6.541l-0.145-0.122c0-0.009-0.001-0.017-0.002-0.025c0.075,0.01,0.136,0.024,0.186,0.038C12.197,6.467,12.186,6.503,12.171,6.541z M10.997,5.676h-0.182l-0.004-0.829c0.34,0.017,0.673,0.062,1.002,0.136c0.271,0.072,0.512,0.495,0.533,0.921c-0.023,0.056-0.043,0.11-0.061,0.159c-0.066,0.044-0.156,0.084-0.263,0.1c0.04-0.329-0.104-0.49-0.104-0.49s-0.146,0.163-0.105,0.49c-0.297-0.042-0.443-0.261-0.443-0.261l0.002,0.756c0,0,0.146-0.219,0.442-0.265c-0.001,0.012-0.002,0.024-0.005,0.035l-0.393,0.283l-0.204,0.001l-0.518-0.183c-0.016-0.04-0.03-0.084-0.039-0.13c0.428,0.043,0.639,0.26,0.639,0.26l-0.003-0.757c0,0-0.21,0.219-0.636,0.266C10.713,5.84,10.997,5.676,10.997,5.676z M9.187,4.993c0.33-0.076,0.667-0.124,1.006-0.142l0.001,0.829L10.013,5.68c0,0,0.284,0.161,0.343,0.488C9.93,6.125,9.719,5.907,9.719,5.907l0.002,0.758c0,0,0.209-0.219,0.638-0.266c-0.008,0.046-0.019,0.088-0.036,0.128L9.794,6.718L9.612,6.719L9.208,6.431C9.205,6.422,9.203,6.414,9.202,6.404c0.296,0.042,0.443,0.261,0.443,0.261L9.643,5.907c0,0-0.146,0.221-0.442,0.266C9.24,5.845,9.098,5.684,9.098,5.684S8.951,5.846,8.993,6.173C8.858,6.155,8.755,6.099,8.682,6.044C8.679,6.036,8.675,6.026,8.672,6.019c-0.006-0.023-0.008-0.04-0.008-0.051C8.663,5.524,8.908,5.069,9.187,4.993z"/></g></svg>'
        },
        circle_next: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 30 30" enable-background="new 0 0 30 30" xml:space="preserve"><g><polygon fill="#FFFFFF" points="5.7177734,23.9873047 14.7363281,14.9677734 5.7177734,5.9482422 11.0234375,0.6420898 25.3496094,14.9677734 11.0234375,29.2929688"/><path fill="#999" d="M11.0234375,1.3491211l13.6191406,13.6186523L11.0234375,28.5859375l-4.5986328-4.5986328l8.3115234-8.3125l0.7070312-0.7070312l-0.7070312-0.7070312l-8.3115234-8.3125L11.0234375,1.3491211 M11.0234375-0.0649414L5.0107422,5.9482422l9.0185547,9.0195312l-9.0185547,9.0195312L11.0234375,30l15.0332031-15.0322266L11.0234375-0.0649414L11.0234375-0.0649414z"/></g></svg>'
        },
        circle_prev: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 30 30" enable-background="new 0 0 30 30" xml:space="preserve"><g><polygon fill="#FFFFFF" points="5.7172852,14.9677734 20.0429688,0.6420898 25.3486328,5.9482422 16.3300781,14.9677734 25.3486328,23.9873047 20.0429688,29.2929688"/><path fill="#999" d="M20.0429688,1.3491211l4.5986328,4.5991211l-8.3115234,8.3125l-0.7070312,0.7070312l0.7070312,0.7070312l8.3115234,8.3125l-4.5986328,4.5986328L6.4243164,14.9677734L20.0429688,1.3491211 M20.0429688-0.0649414L5.0102539,14.9677734L20.0429688,30l6.0126953-6.0126953l-9.0185547-9.0195312l9.0185547-9.0195312L20.0429688-0.0649414L20.0429688-0.0649414z"/></g></svg>'
        },
        zoom: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 30 30" enable-background="new 0 0 30 30" xml:space="preserve"><g><path fill="#FFFFFF" d="M15.0419922,27.4990234c-6.8691406,0-12.4575195-5.5878906-12.4575195-12.4570312S8.1728516,2.5844727,15.0419922,2.5844727s12.4570312,5.5883789,12.4570312,12.4575195S21.9111328,27.4990234,15.0419922,27.4990234z"/><path fill="#999" d="M15.0419922,3.0864258c6.5927734,0,11.9560547,5.3632812,11.9560547,11.9555664c0,6.5927734-5.3632812,11.9560547-11.9560547,11.9560547c-6.5922852,0-11.9555664-5.3632812-11.9555664-11.9560547C3.0864258,8.449707,8.449707,3.0864258,15.0419922,3.0864258 M15.0419922,2.0830078c-7.1567383,0-12.9589844,5.8022461-12.9589844,12.9589844c0,7.1572266,5.8022461,12.9589844,12.9589844,12.9589844c7.1572266,0,12.9589844-5.8017578,12.9589844-12.9589844C28.0009766,7.8852539,22.1992188,2.0830078,15.0419922,2.0830078L15.0419922,2.0830078z"/></g><polygon points="20,14.0205078 16.0517578,14.0205078 16.0517578,10.0415039 14.0517578,10.0415039 14.0517578,14.0205078 10.0419922,14.0205078 10.0419922,16.0205078 14.0517578,16.0205078 14.0517578,20 16.0517578,20 16.0517578,16.0205078 20,16.0205078 " fill="#999" /></svg>'
        },
        votes_frame: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"width="84px" height="84px" viewBox="0 0 84 84" enable-background="new 0 0 84 84" xml:space="preserve"><g><g><path d="M77.1806641,1v32.2700195v0.4140625l0.2929688,0.2929688l5.1123047,5.112793l-5.1123047,5.1132812l-0.2929688,0.2929688v0.4140625v32.2695312H1.0004883V1H77.1806641 M78.1806641,0H0.0004883v78.1796875h78.1801758V44.9101562L84,39.0898438l-5.8193359-5.8198242V0L78.1806641,0z"/></g></g></svg>'
        },
        eye: {
            svg: '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="20px" viewBox="0 0 20 20" enable-background="new 0 0 20 20" xml:space="preserve"><path d="M10,7.521c-2.667,0-5.035,1.281-6.522,3.261c1.487,1.98,3.855,3.261,6.522,3.261s5.034-1.28,6.521-3.261 C15.034,8.802,12.667,7.521,10,7.521z M9.185,9.151C9.635,9.151,10,9.515,10,9.965c0,0.451-0.365,0.816-0.815,0.816 s-0.816-0.365-0.816-0.816C8.369,9.515,8.735,9.151,9.185,9.151z M13.331,12.429c-0.504,0.257-1.039,0.456-1.589,0.59 c-0.566,0.139-1.153,0.208-1.742,0.208c-0.589,0-1.176-0.069-1.742-0.208c-0.551-0.134-1.085-0.333-1.591-0.59 c-0.802-0.411-1.531-0.974-2.135-1.647c0.604-0.673,1.333-1.237,2.135-1.647c0.412-0.211,0.844-0.381,1.289-0.51 C7.702,9.01,7.554,9.471,7.554,9.965c0,1.352,1.096,2.447,2.446,2.447c1.352,0,2.446-1.095,2.446-2.447 c0-0.495-0.148-0.955-0.401-1.341c0.443,0.128,0.874,0.299,1.286,0.51c0.804,0.41,1.531,0.974,2.137,1.647 C14.862,11.453,14.135,12.018,13.331,12.429L13.331,12.429z M14.445,6.548C13.06,5.842,11.565,5.482,10,5.482 c-1.563,0-3.06,0.359-4.445,1.066C4.807,6.932,4.107,7.414,3.478,7.977v1.39c0.706-0.745,1.538-1.372,2.448-1.838 C7.196,6.882,8.566,6.552,10,6.552c1.434,0,2.805,0.33,4.073,0.978c0.909,0.466,1.742,1.093,2.448,1.838v-1.39 C15.893,7.414,15.193,6.932,14.445,6.548L14.445,6.548z"/></svg>'
        }
    },

    update: function () {
        var icons = $$('.b-svg-icon'),
            setBackground = false;

        if (!utils.testSVG()) {
            setBackground = true;
        }

        icons.each(function (item, i) {
            if (item.get('data-name') && this.icons[item.get('data-name')]) {
                if (setBackground) {
                    // Элементу проставляется фоновое изображение
                    item.setStyle('background-image', 'url(' + this.spriteUrl + ')');
                } else {
                    // Внутрь элемента вставляется svg-иконка
                    item.innerHTML = this.icons[item.get('data-name')]['svg'];
                }
            }
        }.bind(this));

    }
};
subsiteSettingsHandler = {
    subscribe: function (button, domain) {
        subsiteSettingsHandler.setSubscription(button, 'on', domain);
    },
    unsubscribe: function (button, domain) {
        subsiteSettingsHandler.setSubscription(button, 'off', domain);
    },
    setSubscription: function (button, blog_switch, domain) {
        var data = 'switch=' + blog_switch;
        if (domain) {
            data += '&domain=' + domain;
        }
        new futuAjax({
            button: $(button),
            color_to: '0.5',
            color_from: '1',
            attribute: 'opacity',
            url: ajaxUrls.feeds,
            data: data,
            onLoadFunction: function (response) {
                var subscription_holder = $(button).getParent('.b-subsite_controls');
                var subsribers_delta = 0;
                if (blog_switch == 'on') {
                    subscription_holder.addClass('js-subscribed');
                    subsribers_delta++;
                } else {
                    subscription_holder.removeClass('js-subscribed');
                    subsribers_delta--;
                }
                if (subscription_holder.getParent('.b-blogs_list_item')) {
                    var subscribers_count_element = subscription_holder.getParent('.b-blogs_list_item').getElement('.b-subsite_controls_subscribers');
                    var current_subscribers_count = parseInt(subscribers_count_element.getElement('strong').innerHTML);
                    subscribers_count_element.innerHTML = '<strong>' + (current_subscribers_count + subsribers_delta) + '</strong><br>' + utils.getPlural(current_subscribers_count + subsribers_delta, ['подписчик', 'подписчика', 'подписчиков']);
                }
            }
        });
    },
    toggleBlogInfo: function () {
        var blog_info_element = $('js-blog_info');
        blog_info_element.getElement('.b-i-blog_info').set('morph', {duration: 222, onComplete: function () {
            if (!blog_info_element.hasClass('js-opened')) {
                blog_info_element.removeClass('js-opened')
                blog_info_element.addClass('hidden')
            }
        }});
        if (blog_info_element.hasClass('hidden')) {
            blog_info_element.removeClass('hidden');
            blog_info_element.addClass('js-opened')
            blog_info_element.getElement('.b-i-blog_info').morph({
                marginTop: 0,
                marginBottom: 10
            });
        } else {
            blog_info_element.removeClass('js-opened')
            blog_info_element.getElement('.b-i-blog_info').morph({
                marginTop: -blog_info_element.getElement('.b-i-blog_info').getSize().y,
                marginBottom: 0
            });
        }

    }
};

blogsSettingsHandler = {
    isOneLineRuleSupported: null,
    bgPosition: null,
    custom_style_parent: 'html',
    blog_colors_styles: {
        'inputs_bg_color': {
            'backgroundColor': [
                '.i-form_text_input',
                '.i-form_textarea',
                '.b-search_form .b-fui_icon_button_search',
                '.b-header_search .b-fui_icon_button_search'
            ]
        },
        'inputs_text_blur_color': {
            'color': [
                '.i-form_text_input',
                '.i-form_textarea',
                '.i-form_text_input::-webkit-input-placeholder',
                '.i-form_text_input:-moz-placeholder',
                '.i-form_text_input:-ms-input-placeholder'
            ]
        },
        'inputs_text_color': {
            'color': [
                '.i-form_text_input:focus',
                '.i-form_textarea:focus',
                'a.b-fui_icon_button_search .b-svg-icon'
            ],
            'fill': [
                'a.b-fui_icon_button_search .b-svg-icon svg path'
            ]
        },
        'inputs_border_color': {
            'borderColor': [
                '.i-form_text_input',
                '.i-form_textarea'
            ]
        },
        'inputs_border_top_color': {
            'borderTopColor': [
                '.i-form_text_input',
                '.i-form_textarea'
            ]
        },
        'text_color': {
            'color': [
                'body',
                '.comments_add_block_bottom a.comments_add_block_bottom_link',
                '.b-form_field .b-form_radio_label',
                '.b-comments_reply_block_delete_file',
                '.b-header_counters a u',
                '.b-elections_candidate_info_program',
                '.b-form_field .active label',
                '.b-header_nav .b-menu_item__user_menu .b-svg-icon',
                '.b-header_nav_user_close .b-svg-icon',
                '.b-voter',
                '.b-voter a'
            ],
            'fill': [
                '.l-wrapper .b-svg-icon svg path',
                '.l-wrapper .b-svg-icon svg rect',
                '.l-wrapper .b-svg-icon svg polygon'
            ]
        },
        'header_color': {
            'color': [
                '.b-blog_nav_sort a.b-blog_nav_sort_link',
                '.b-subsite_header h1 a',
                '.threshold_select_button'
            ],
            'backgroundColor': [
                '.b-blog_nav_sort strong.b-blog_nav_sort_link',
                '.b-comments_controls_new_comment',
                '.paginator span strong em',
                '.paginator .scroll_thumb',
                '.paginator .current_page_mark',
                '.b-elections_header'
            ]
        },
        'links_color': {
            'color': [
                'a',
                '.post h3',
                '.post h3 a',
                '.b-post_edit_controls .b-post_edit_submit i',
                '.post .post_body .b-cut_link',
                '.b-elections_candidate_info_name',
                '.b-elections_candidate_info a',
                '.b-external_image .b-open_link',
                '.b-external_image .b-open_link:visited',
                '.b-header_nav .b-svg-icon',
                'a.b-fui_icon_button_get_a_life .b-svg-icon',
                'a .b-svg-icon',
                '.b-candidate',
                '.selected.b-author_comment .c_show_user',
                '.selected.post .c_show_user',
                '.b-domain_bans .c_user'
            ],
            'fill': [
                '.b-header_nav .b-svg-icon svg path',
                '.b-header_nav .b-svg-icon svg rect',
                '.b-header_nav .b-svg-icon svg polygon',
                'a.b-fui_icon_button_get_a_life .b-svg-icon svg path',
                'a.b-fui_icon_button_get_a_life .b-svg-icon svg rect',
                'a.b-fui_icon_button_get_a_life .b-svg-icon svg polygon',
                'a .b-svg-icon svg path',
                'a .b-svg-icon svg rect',
                'a .b-svg-icon svg polygon'
            ],
            'backgroundColor': [
                '.b-file_uploader_drag_hover',
                '.b-load_more_posts_button',
                '.b-header_expand_top_panel:hover span',
                '.b-header_expand_top_panel:hover:before',
                '.b-header_expand_top_panel:hover:after'
            ],
            'outlineColor': [
                '.i-form_text_input:focus',
                '.i-form_textarea:focus'
            ],
            'borderColor': [
                '.b-author_comment .b-comment_outline'
            ]
        },
        'links_visited_color': {
            'color': [
                '.post .dt a:visited',
                '.c_body a:visited'
            ]
        },
        'links_system_color': {
            'color': [
                '.b-footer_nav',
                '.b-footer_nav a',
                '.b-form_field_description',
                '.dd',
                '.dd a',
                '.b-form_submit_description',
                '.b-blog_nav_threshold',
                '.c_footer',
                '.c_user',
                '.c_answer',
                '.c_parent',
                '.c_previous',
                '.comments_header_threshhold',
                '.b-post_edit_controls .b-post_edit_cancel i',
                '.b-form_field label',
                '.comments_header_threshhold',
                '.b-comments_header_new_selector i',
                '.b-post_tags .tag',
                '.b-post_tags',
                '.vote_result',
                '.b-menu_item_active .b-menu_link',
                '.b-fui_icon_button.b-fui_icon_button_information i',
                '.b-fui_icon_button.b-fui_icon_button_settings i',
                'a.b-fui_icon_button i',
                '.b-header_counters a',
                '.vote .vote_voted',
                '.c_show_user',
                '.b-domain_subscription_text',
                '.b-blog_controls_sub_item',
                'a.b-blog_controls_sub_item_link',
                '.paginator_pages',
                '.paginator a',
                '.b-comments_ignored_description',
                '.b-blog_controls_elections_settings label',
                '.b-blog_info_government_president_elections_date',
                '.b-blog_info_government_president_toggle_impeachment',
                '.b-blog_info_government_president_impeachment_demand',
                '.b-elections_candidate_info_stats',
                '.b-comments_reply_block_container_limit_content',
                '.b-elections_voting_data',
                '.b-sys_link',
                '.b-sys_text',
                '.b-elections_voting_data .b-removed_user',
                '.b-elections_voting_data .b-removed_user',
                '.b-header_expand_top_panel i',
                '.b-blog_controls_toggle_main_feed .b-form_radio_label',
            ],
            fill: [
                '.b-header_nav .b-menu_item__user_menu .b-svg-icon svg path',
                '.b-header_nav_user_close .b-svg-icon svg path',
                '.b-header_nav .b-fui_icon_button_inbox .b-svg-icon svg path',
                '.b-header_nav .b-fui_icon_button_inbox .b-svg-icon svg rect',
                '.b-header_nav .b-fui_icon_button_inbox .b-svg-icon svg polygon',
                '.b-header_nav .b-fui_icon_button_interest .b-svg-icon svg path',
                '.b-header_nav .b-fui_icon_button_interest .b-svg-icon svg rect',
                '.b-header_nav .b-fui_icon_button_interest .b-svg-icon svg polygon',
                '.b-header_nav .b-fui_icon_button_favourites .b-svg-icon svg path',
                '.b-header_nav .b-fui_icon_button_favourites .b-svg-icon svg rect',
                '.b-header_nav .b-fui_icon_button_favourites .b-svg-icon svg polygon',
                '.b-post_footer_opts .b-fui_icon .b-svg-icon svg path'
            ],
            'borderColor': [
                'a.c_show_user:hover'
            ],
            'backgroundColor': [
                '.paginator a:hover em',
                '.b-header_expand_top_panel span',
                '.b-header_expand_top_panel:before',
                '.b-header_expand_top_panel:after'
            ]
        },
        'irony_color': {
            'color': [
                '.irony'
            ]
        },
        'moderator_color': {
            'color': [
                '.moderator'
            ]
        },
        'background_color': {
            'backgroundColor': [
                'body',
                '.b-elections_candidate_info'
            ],
            'color': [
                '.b-file_uploader_drag_hover',
                '.b-blog_nav_sort strong.b-blog_nav_sort_link',
                '.b-load_more_posts_button',
                '.b-comments_controls_new_comment',
                '.paginator span',
                '.paginator a:hover em',
                'a.b-calendar_day:hover',
                '.b-elections_status',
                '.b-elections_timer'
            ],
            'borderRightColor': [
                '.b-i-elections_candidate_info_arrow'
            ]
        },
        'tagline_border_color': {
            'borderColor': [
                '.b-header_tagline',
                '.b-blog_controls_pic',
                '.b-i-blog_info',
                '.b-blog_controls_toggle_adult_field',
                '.b-footer_nav_section_user',
                '.b-blog_controls_post_preview',
                '.b-tags',
                '.b-elections_candidate_info',
                '.b-sidebar_item_content',
                '.b-ads-big_banner_border'
            ],
            'borderRightColor': [
                '.b-header_tagline_arrow',
                '.b-elections_candidate_info_arrow'
            ],
            'borderLeftColor': [
                '.b-footer_nav_section_info',
                '.b-blog_info_government',
                '.b-login_signup_container__right .b-header_tagline_arrow',
                '.b-user_ad'
            ],
            'backgroundColor': [
                '.b-header_counters a.b-header_counters_subscriptions'
            ]
        },
        'tagline_text_color': {
            'color': [
                '.b-i-header_tagline',
                '.b-header_tagline_logout .b-fui_icon_button_logout i',
                '.b-i-blog_info',
                '.b-form_field .b-blog_controls_toggle_adult_field .b-form_radio_label',
                '.b-footer_nav .b-footer_nav_section_user a',
                '.b-blog_controls_toggle_adult_field .b-form_radio_label',
                '.b-user_ad',
                '.b-user_ad .b-ad_link',
                '.b-user_ad_list .b-ad_link'
            ]
        },
        'tagline_links_color': {
            'color': [
                '.b-i-header_tagline a',
                '.b-i-blog_info a',
                '.b-footer_nav_section_user a span',
                '.b-tags .b-cloud a',
                '.b-cloud a:visited',
                '.b-user_ad .c_footer',
                '.b-user_ad .c_footer a',
                '.b-header_counters .b-header_counters_subscriptions i'
            ]
        },
        'information_bg_color': {
            'backgroundColor': [
                '.b-i-blog_info',
                '.b-blog_controls_toggle_adult_field',
                '.b-i-header_tagline',
                '.b-footer_nav_section_user',
                '.b-tags',
                '.b-user_ad'
            ],
            'borderRightColor': [
                '.b-i-header_tagline_arrow',
                '.b-login_signup_container .b-i-header_tagline_arrow'
            ],
            'borderLeftColor': [
                '.b-login_signup_container__right .b-i-header_tagline_arrow'
            ]
        },
        'rating_bg_color': {
            'backgroundColor': [
                '.b-menu_link',
                '.vote_button',
                '.over a.vote_voted',
                '.post .over a.vote_voted',
                '.comment .over a.vote_voted',
                '.l-touch_capable .c_vote .vote_voted',
                '.l-touch_capable .dd .vote_voted',
                '.paginator .scroll_trough'
            ]
        },
        'rating_border_color': {
            'borderColor': [
                '.vote_button',
                '.over a.vote_voted',
                '.l-touch_capable .c_vote .vote_voted',
                '.l-touch_capable .dd .vote_voted'
            ],
            'borderBottomColor': [
                '.b-menu_link',
                '.paginator .scroll_trough'
            ]
        },
        'rating_shadow_color': {
            'borderTopColor': [
                '.b-menu_link_text'
            ]
        },
        'rating_plus_color': {
            'color': [
                '.vote .vote_button'
            ]
        },
        'rating_plus_active_color': {
            'color': [
                '.vote_button:hover',
                'a.vote_voted',
                '.vote a.vote_voted',
                '.b-votes_popup .over a.vote_voted',
                '.l-touch_capable .c_vote .vote_voted',
                '.l-touch_capable .dd .vote_voted',
                '.b-menu_link'
            ]
        },
        'windows_bg_color': {
            'backgroundColor': [
                '.b-blog_controls_bg_pic_position',
                '.b-textarea_editor',
                '.b-form_submit',
                '.b-post_render_types',
                '.b-comments_header_new_selector em',
                '.b-votes_popup',
                '.b-futu_controls ul',
                '.b-header_nav_user_menu',
                'a.threshold_select_option',
                '.b-post_tags .tag',
                '.mine .vote_result',
                '.b-calendar_controls',
                '.b-archive_navigation',
                '.b-calendar_month_header',
                'a.b-calendar_day:hover',
                '.b-notification',
                '.b-popup_settings_form',
                '.b-blog_controls_elections_settings',
                '.b-elections_nomination',
                '.b-elections_candidates',
                '.tip-title',
                '.b-tip_arrow'
            ],
            'borderColor': [
                '.b-calendar_controls',
                '.b-archive_navigation',
                '.b-calendar_month_header',
                '.b-calendar_month_days'
            ]
        },
        'windows_text_color': {
            'color': [
                '.b_users_table-subtitle',
                '.b-no_votes',
                '.b_users_table-list',
                'a.threshold_select_option',
                '.b-header_nav_user_menu a.b-fui_icon_button_logout i',
                '.b-comments_header_new_selector em',
                '.b-votes_popup .vote_result',
                '.mine .vote_result',
                '.b-textarea_editor a',
                '.b-votes_popup .vote .vote_voted',
                '.b-archive_navigation i',
                '.b-calendar_month_header',
                '.b-notification',
                '.b-notification-title',
                '.b-blog_controls_elections_settings',
                '.b-blog_controls_elections_settings .threshold_select_button',
                '.b-blog_controls_elections_settings .b-blog_controls_elections_settings_block_caption a',
                '.b-elections_nomination',
                '.b-elections_candidates',
                '.b-elections_candidate_votes_counter',
                '.tip-title'
            ],
            'fill': [
                'a.b-fui_icon_button_logout .b-svg-icon svg path'
            ]
        },
        'windows_links_color': {
            'color': [
                '.b_users_table-link',
                '.b-futu_controls .b-futu_controls_item a',
                '.b-header_nav_user_menu a',
                'a.threshold_select_option_selected',
                '.b-header_nav_user_menu a.b-fui_icon_button_logout:hover i',
                '.b-header_nav_user_menu a.b-fui_icon_button_logout:active i',
                '.b-textarea_editor .b-textarea_editor_link',
                '.b-archive_navigation a',
                '.b-notification a',
                '.b-blog_controls_elections_settings a',
                '.b-blog_controls_elections_settings input:checked + label',
                '.b-elections a'
            ]
        },
        'windows_dark_color': {
            'borderBottomColor': [
                '.threshold_select_options_holder',
                '.b-header_nav_user_menu',
                '.b-header_nav_user_menu_item_write',
                '.b-header_nav_user_menu_item_write div',
                '.b-header_nav_user_menu_item em em',
                '.b-votes_popup',
                '.b-futu_controls ul',
                '.b_users_table-cell:first-child',
                '.b-post_tags .tag',
                '.b-futu_controls_item span'
            ],
            'borderRightColor': [
                '.b_users_table-cell:first-child',
                '.b-post_tags .tag'
            ],
            'borderTopColor': [
                'a.threshold_select_option em'
            ],
            'borderColor': [
                '.b-popup_settings_form',
                '.tip-wrap',
                '.b-tip_arrow'
            ]
        },
        'windows_bright_color': {
            'borderBottomColor': [
                '.b-header_nav_user_menu_item em'
            ],
            'borderTopColor': [
                '.threshold_select_option_text',
                '.b-futu_controls_item span'
            ],
            'borderLeftColor': [
                '.b_users_table-cell'
            ]
        },
        'new_comments_bg_color': {
            'backgroundColor': [
                '.highlight1 .new .comment_inner',
                '.highlight2 .new .comment_inner',
                '.highlight3 .new .comment_inner',
                '.highlight4 .new .comment_inner'
            ]
        }
    },
    initColors: function () {
        window.addEvent('domready', function () {
            var domain_colors = [];
            $A($(document.body).getElements('.b-blog_controls_color')).each(function (color_element) {
                domain_colors.push(color_element.id.substr(9));
            });
            window.myMooRainbows = [];
            window.currentMooColor = false;
            window.currentMooInput = false;
            var startColor = new Color('000000');
            var globalMooRainbow = new MooRainbow('js-default_moo_rainbow', {
                id: 'default_moo_rainbow',
                imgPath: '/static/i/moorainbow/',
                wheel: true,
                startColor: startColor.rgb,
                onChange: function (color) {
                    var color_id = $(window.currentMooColor).id.substr(9);
                    var color_hex = color.hex.substr(1);
                    var color_rgb = color.rgb;
                    blogsSettingsHandler.setColor(color_id, color_hex, color_rgb);
                },
                onComplete: function (color) {
                    var color_id = $(window.currentMooColor).id.substr(9);
                    var color_hex = color.hex.substr(1);
                    blogsSettingsHandler.saveColor(color_id, color_hex);
                }
            });
            $A(domain_colors).each(function (domain_color) {
                $('js-color_' + domain_color).addEvent('click', function (e) {
                    if (globalMooRainbow.visible) {
                        var color_id = $(window.currentMooColor).id.substr(9);
                        var color_hex = globalMooRainbow.hexInput.value.substr(1);
                        blogsSettingsHandler.saveColor(color_id, color_hex);
                    }
                    e = new Event(e);
                    e.stopPropagation();
                    globalMooRainbow.toggle({
                        color: $('js-color_' + domain_color).getElement('input').value,
                        domain_color: domain_color
                    });
                });
                //$('color_' + domain_color).set('styles', {'background-color' : '#' + $('input_' + domain_color).value});
            });
        });
    },
    setColor: function (color_id, color_hex, color_rgb, is_new_rule) {
        Colors[color_id] = '#' + color_hex;
        var color_hex_css = '#' + color_hex;

        if (this.isOneLineRuleSupported === null) {
            this.isOneLineRuleSupported = !(Browser.name == 'ie' && Browser.version < 9);
        }

        var color_attributes = blogsSettingsHandler.blog_colors_styles[color_id];
        if (color_attributes) {
            for (var attribute in color_attributes) {
                if (color_attributes.hasOwnProperty(attribute)) {
                    var rules = color_attributes[attribute];

                    if (this.isOneLineRuleSupported) {
                        rules = rules.map(function (rule) {
                            if (rule == 'body' && blogsSettingsHandler.custom_style_parent != 'html') {
                                return blogsSettingsHandler.custom_style_parent;
                            } else {
                                return blogsSettingsHandler.custom_style_parent + ' ' + rule;
                            }
                        });
                        blogsSettingsHandler.setCSSProperty(rules.join(', '), attribute, color_hex_css, is_new_rule);
                    } else {
                        for (var i = 0; i < rules.length; i++) {
                            var rule = '';
                            if (rule == 'body' && blogsSettingsHandler.custom_style_parent != 'html') {
                                rule = blogsSettingsHandler.custom_style_parent;
                            } else {
                                rule = blogsSettingsHandler.custom_style_parent + ' ' + rules[i];
                            }
                            blogsSettingsHandler.setCSSProperty(rule, attribute, color_hex_css, is_new_rule);
                        }
                    }
                }
            }
        }

        var current_color_container = $('js-color_' + color_id);
        if (current_color_container) {
            if (current_color_container.hasClass('js-color_text')) {
                current_color_container.set('styles', {'color': color_hex_css});
            }
            current_color_container.getElement('.b-blog_controls_color_box').set('styles', {'background-color': color_hex_css});
            current_color_container.getElement('input').value = color_hex_css;
        }

        var browser_gradient_styles = [
            '-moz-linear-gradient',
            '-webkit-linear-gradient',
            '-o-linear-gradient',
            '-ms-linear-gradient',
            'linear-gradient'
        ];

        if (color_id == 'footer_gradient_color') {
            for (var i = 0; i < browser_gradient_styles.length; i++) {
                blogsSettingsHandler.setCSSProperty(blogsSettingsHandler.custom_style_parent + ' .l-footer', 'background', '{style}(top,  rgba({color_0}, {color_1}, {color_2}, 0) 0, rgba({color_0}, {color_1}, {color_2}, 0) 90px, rgba({color_0}, {color_1}, {color_2}, 0.15) 176px)'.substitute({
                    style: browser_gradient_styles[i],
                    color_0: color_rgb[0],
                    color_1: color_rgb[1],
                    color_2: color_rgb[2]
                }), is_new_rule);
                blogsSettingsHandler.setCSSProperty(blogsSettingsHandler.custom_style_parent + ' .l-header', 'background', '{style}(top,  rgba({color_0}, {color_1}, {color_2}, 0.15) 0, rgba({color_0}, {color_1}, {color_2}, 0) 50px)'.substitute({
                    style: browser_gradient_styles[i],
                    color_0: color_rgb[0],
                    color_1: color_rgb[1],
                    color_2: color_rgb[2]
                }), is_new_rule);
                blogsSettingsHandler.setCSSProperty(blogsSettingsHandler.custom_style_parent + ' .b-comments_controls', 'background', '{style}(top,  rgba({color_0}, {color_1}, {color_2}, 0.15) 0, rgba({color_0}, {color_1}, {color_2}, 0) 50px)'.substitute({
                    style: browser_gradient_styles[i],
                    color_0: color_rgb[0],
                    color_1: color_rgb[1],
                    color_2: color_rgb[2]
                }), is_new_rule);
            }
        }

        if (color_id == 'rating_shadow_color') {
            blogsSettingsHandler.setCSSProperty(blogsSettingsHandler.custom_style_parent + ' .vote_button, ' + blogsSettingsHandler.custom_style_parent + ' .over a.vote_voted, ' + blogsSettingsHandler.custom_style_parent + ' .l-touch_capable .c_vote .vote_voted', 'boxShadow', 'inset 0px 3px 1px -2px {color}'.substitute({
                color: color_hex_css
            }), is_new_rule);
        }

        if (color_id == 'new_comments_bg_color') {
            blogsSettingsHandler.setCSSProperty(blogsSettingsHandler.custom_style_parent + ' .highlight4 .new .comment_inner', 'border', 'none', is_new_rule);
        }


    },
    setAllStyles: function () {
        for (var color_id in Colors) {
            if (Colors.hasOwnProperty(color_id)) {
                var color_hex = Colors[color_id];
                var color_rgb = new Color(color_hex);
                blogsSettingsHandler.setColor(color_id, color_hex.substr(1), color_rgb, true);
            }
        }
        // Подписываемся на событие изменения лейаута,
        // чтобы спозиционировать фон со смещением для устройств с маленьким разрешением
        utils.addListener(document, 'update_layout', this.setAdaptiveBodyBackground.bind(this));
    },
    saveColor: function (color_id, color_hex) {
        var data = color_id + '=' + color_hex;
        new futuAjax({
            button: $('js-color_' + color_id).getElement('.b-blog_controls_color_box'),
            attribute: 'borderColor',
            color_to: '#FFFFFF',
            color_from: '#000000',
            remove_element_color: false,
            url: ajaxUrls.controls_edit,
            data: data,
            onLoadFunction: function (response) {

            }
        });
    },
    setCSSProperty: function (rule, attribute, value, is_new_rule) {
        if (is_new_rule) {
            try {
                utils.addCSSRule(rule, utils.camelToDash(attribute), value);
            } catch (err) {
            }
        } else {
            var cssRule = utils.getCSSRule(rule);
            if (cssRule.style && cssRule.style[attribute]) {
                cssRule.style[attribute] = value;
            } else {
                try {
                    utils.addCSSRule(rule, utils.camelToDash(attribute), value);
                } catch (err) {
                }
            }
        }
    },
    resetColor: function (color_id, color_hex) {
        var data = color_id + '=' + color_hex;
        var color_rgb = new Color(color_hex);
        new futuAjax({
            button: $('js-color_' + color_id).getElement('.b-blog_controls_color_box'),
            attribute: 'borderColor',
            color_to: '#FFFFFF',
            color_from: '#000000',
            remove_element_color: false,
            url: ajaxUrls.controls_edit,
            data: data,
            onLoadFunction: function (response) {
                blogsSettingsHandler.setColor(color_id, color_hex, color_rgb);
            }
        });
    },
    resetPic: function (pic_id, pic_url) {
        var data = pic_id + '=';
        new futuAjax({
            button: $('js-file_uploader_button_' + pic_id),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            remove_element_color: false,
            url: ajaxUrls.controls_edit,
            data: data,
            onLoadFunction: function (response) {
                blogsSettingsHandler.setPic(pic_id, pic_url);
            }
        });
    },
    initFileUploader: function (pic_id) {
        if (!utils.isFileUploadSupported()) {
            $('js-file_uploader_' + pic_id).addClass('hidden');
            return;
        }
        var dragContainer = $('js-file_uploader_drag_' + pic_id);

        new futuFileUploader({
            container: 'js-file_uploader_' + pic_id,
            browseButton: 'js-file_uploader_button_' + pic_id,
            dropElement: 'js-file_uploader_drag_' + pic_id,
            dropElementNode: $('js-file_uploader_drag_' + pic_id),
            uploadProgress: function (up, file) {
                dragContainer.innerHTML = file.name + '&nbsp;(' + file.percent + '%)';
            }.bind(this),
            uploadComplete: function (up, file, response) {
                ajaxHandler.highlightField($('js-file_uploader_' + pic_id), Colors.background_color, Colors.links_color);
            }.bind(this),
            fileUploaded: function (up, file, response) {
                var response = JSON.decode(response.response);
                if (response.media_id) {
                    var data = pic_id + '=' + response.media_id;
                    new futuAjax({
                        button: $('js-file_uploader_button_' + pic_id),
                        attribute: 'opacity',
                        color_to: 0.5,
                        color_from: 1,
                        remove_element_color: false,
                        url: ajaxUrls.controls_edit,
                        data: data,
                        onLoadFunction: function (response) {
                            if (response[pic_id]) {
                                blogsSettingsHandler.setPic(pic_id, response[pic_id]);
                            }
                        }
                    });
                } else {
                    if (response.status == 'ERR') {
                        for (var i = 0; i < response.errors.length; i++) {
                            ajaxHandler.alertError(localMessages.getErrorMessageByError(response.errors[i]));
                        }
                        return false;
                    }
                }
            }
        });
    },
    setPic: function (pic_id, pic_url) {
        if ($('js-file_pic_' + pic_id)) {
            if ($('js-file_pic_' + pic_id).getElement('img')) {
                $('js-file_pic_' + pic_id).getElement('img').src = pic_url;
            } else {
                $('js-file_pic_' + pic_id).innerHTML = '<img src="' + pic_url + '">';
            }
        }

        if (pic_id == 'bg') {
            var bg_pic_position_y = $(document).getElement('input[name="bg_pic_position_y"]:checked').value;
            var bg_pic_repeat = $(document).getElement('input[name="bg_pic_repeat"]:checked');
            var rule = blogsSettingsHandler.custom_style_parent == 'html' ? 'html .l-i-wrapper' : blogsSettingsHandler.custom_style_parent;
            if (bg_pic_position_y == 'middle') {
                bg_pic_position_y = 'center';
            }
            if (pic_url.length > 0) {
                blogsSettingsHandler.setCSSProperty(rule, 'backgroundImage', 'url(' + pic_url + ')');
            } else {
                blogsSettingsHandler.setCSSProperty(rule, 'backgroundImage', 'none');
            }
            if (bg_pic_repeat) {
                blogsSettingsHandler.setCSSProperty(rule, 'backgroundRepeat', bg_pic_repeat.value);
            }
            blogsSettingsHandler.setCSSProperty(rule, 'backgroundPosition', $(document).getElement('input[name="bg_pic_position_x"]:checked').value + ' ' + bg_pic_position_y);
        }

        if (pic_id == 'logo') {
            $('js-logo').style.backgroundImage = 'url(' + pic_url + ')';
        }
    },
    setBodyBackground: function (button) {
        var wrapper = $$('.l-i-wrapper');
        var bg_pic_position_x = $(document).getElement('input[name="bg_pic_position_x"]:checked').value;
        var bg_pic_position_y = $(document).getElement('input[name="bg_pic_position_y"]:checked').value;

        var bg_pic_repeat = $(document).getElement('input[name="bg_pic_repeat"]:checked').value;

        if (button) {
            var data = 'bg_pic_position_x=' + bg_pic_position_x + '&bg_pic_position_y=' + bg_pic_position_y + '&bg_pic_repeat=' + bg_pic_repeat;
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                remove_element_color: false,
                url: ajaxUrls.controls_edit,
                data: data,
                onLoadFunction: function (response) {

                }
            });
        }
        if (bg_pic_position_y == 'middle') {
            bg_pic_position_y = 'center';
        }
        if (bg_pic_position_y == 'center' && bg_pic_position_x == 'center') {
            blogsSettingsHandler.setCSSProperty('html .l-i-wrapper', 'backgroundAttachement', 'fixed');
            blogsSettingsHandler.setCSSProperty('html .l-i-wrapper', 'backgroundSize', 'cover');
            blogsSettingsHandler.setCSSProperty('html .l-i-wrapper', 'backgroundPosition', '0 0');
        } else {
            blogsSettingsHandler.setCSSProperty('html .l-i-wrapper', 'backgroundAttachement', 'scroll');
            blogsSettingsHandler.setCSSProperty('html .l-i-wrapper', 'backgroundSize', 'auto');
            blogsSettingsHandler.setCSSProperty('html .l-i-wrapper', 'backgroundRepeat', bg_pic_repeat);
            blogsSettingsHandler.setCSSProperty('html .l-i-wrapper', 'backgroundPosition', bg_pic_position_x + ' ' + bg_pic_position_y);
        }
        if (utils.getBackgroundPosition(wrapper)) {
            blogsSettingsHandler.bgPosition = utils.getBackgroundPosition(wrapper).split(' ');
        }
        this.setAdaptiveBodyBackground();
    },
    // Смещение фона слева для маленьких разрешений на ширину скрытой левой колонки с гертрудой
    setAdaptiveBodyBackground: function () {
        var body = $(document.body),
            wrapper = $$('.l-i-wrapper'),
            position;

        if (utils.getBackgroundPosition(wrapper)) {
            if (this.bgPosition === null) {
                this.bgPosition = utils.getBackgroundPosition(wrapper).split(' ');
            }
            if (body.hasClass('l-800') && parseInt(this.bgPosition[0], 10) === 0) {
                position = '-165px ' + this.bgPosition[1];
            } else {
                position = this.bgPosition[0] + ' ' + this.bgPosition[1];
            }
            var rule = blogsSettingsHandler.custom_style_parent == 'html' ? 'html .l-i-wrapper' : 'html ' + blogsSettingsHandler.custom_style_parent;
            this.setCSSProperty(rule, 'backgroundPosition', position);

        }
    },
    gertrudasHandler: {
        initNewFileUploader: function (pic_id) {
            if (!utils.isFileUploadSupported()) {
                $('js-file_uploader_gertruda').addClass('hidden');
                return;
            }
            var dragContainer = $('js-file_uploader_drag_' + pic_id);

            new futuFileUploader({
                container: 'js-file_uploader_' + pic_id,
                browseButton: 'js-file_uploader_button_' + pic_id,
                dropElement: 'js-file_uploader_drag_' + pic_id,
                dropElementNode: dragContainer,
                uploadProgress: function (up, file) {
                    dragContainer.innerHTML = file.name + '&nbsp;(' + file.percent + '%)';
                }.bind(this),
                uploadComplete: function (up, file, response) {
                    ajaxHandler.highlightField($('js-file_uploader_' + pic_id), Colors.background_color, Colors.links_color);
                }.bind(this),
                fileUploaded: function (up, file, response) {
                    var response = JSON.decode(response.response);
                    if (response.media_id) {
                        var data = 'type=1&media=' + response.media_id;
                        new futuAjax({
                            button: $('js-file_uploader_button_' + pic_id),
                            attribute: 'opacity',
                            color_to: 0.5,
                            color_from: 1,
                            remove_element_color: false,
                            url: ajaxUrls.media_set_add,
                            data: data,
                            onLoadFunction: function (response) {
                                var new_gertruda_element = new Element('li', {
                                    'id': 'js-gertruda_' + response.set_element.id,
                                    'html': '<a href="#" title="Удалить гертруду" class="b-blog_controls_delete_gertruda b-fui_icon_button_close" onclick="blogsSettingsHandler.gertrudasHandler.deleteGertruda(\'{gertruda_id}\'); return false;"><span></span></a><img src="{gertruda_location}" alt="">'.substitute({
                                        'gertruda_id': response.set_element.id,
                                        'gertruda_location': response.set_element.location
                                    })
                                });
                                new_gertruda_element.inject($('js-blog_controls_gertrudas'), 'top');
                            }
                        });
                    } else {
                        if (response.status == 'ERR') {
                            for (var i = 0; i < response.errors.length; i++) {
                                ajaxHandler.alertError(localMessages.getErrorMessageByError(response.errors[i]));
                            }
                            return false;
                        }
                    }
                }
            });
        },
        deleteGertruda: function (gertruda_id) {
            var data = 'id=' + gertruda_id;
            var gertruda_element = $('js-gertruda_' + gertruda_id);
            new futuAjax({
                button: gertruda_element,
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                remove_element_color: false,
                url: ajaxUrls.media_set_remove,
                data: data,
                onLoadFunction: function (response) {
                    gertruda_element.set('morph', {duration: 222, onComplete: function () {
                        gertruda_element.destroy();
                    }});
                    gertruda_element.morph({'height': 0, 'min-height': 0, 'max-height': 0, 'marginBottom': 0});
                }
            });
        }
    },
    toggleDomainSettings: function (checkbox, type) {
        var data;
        if (type == 'adult') {
            data = 'adult=';
        } else if (type == 'main_feed') {
            data = 'exclude=';
        }

        if (checkbox.checked) {
            data += '1';
        } else {
            data += '0';
        }
        data += '&domain=' + globals.domain.id;
        new futuAjax({
            button: $(checkbox),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.controls_edit,
            data: data,
            onLoadFunction: function (response) {
            }
        });
    },
    saveTextControls: function () {
        var data = $('js-blog_settings_text_form').toQueryString();

        new futuAjax({
            button: $('js-blog_settings_text_form_submit'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.controls_edit,
            data: data,
            onLoadFunction: function (response) {
                $(document).getElement('title').innerHTML = (response.title.length > 0) ? response.title : 'd3.ru';
                $(document).getElement('.b-subsite_header h1 a').innerHTML = (response.name.length > 0) ? response.name : document.location.hostname;
                new futuAlert('Текстовые настройки сохранены.');
            }
        });
    },
    toggleGreetings: function (greeting_checkbox) {
        var data = 'domain_greetings=';
        if (greeting_checkbox.checked) {
            data += '1';
        } else {
            data += '0';
        }
        new futuAjax({
            button: greeting_checkbox,
            attribute: 'opacity',
            color_to: 1,
            color_from: 1,
            url: ajaxUrls.controls_edit,
            data: data,
            onLoadFunction: function (response) {
                if (response.domain_greetings == 1) {
                    new futuAlert('Собственные приветствия включены.');
                } else {
                    new futuAlert('Вы выключили режим собственных приветствий.');
                }
            }
        });
    },
    validateGreeting: function (greeting_element) {
        if (greeting_element.value.trim().length < 1) {
            ajaxHandler.highlightField(greeting_element);
            return false;
        }
        return true;
    },
    saveGreeting: function (e, greeting_element, greeting_id) {
        if (!e || (e && e.keyCode == 13)) {
            if (blogsSettingsHandler.validateGreeting(greeting_element)) {
                var url = ajaxUrls.greetings_add;
                var data = 'body=' + encodeURIComponent(greeting_element.value);
                if (greeting_id) {
                    url = ajaxUrls.greetings_edit;
                    data += '&id=' + greeting_id;
                }
                new futuAjax({
                    button: greeting_element,
                    attribute: 'opacity',
                    color_to: 0.5,
                    color_from: 1,
                    url: url,
                    data: data,
                    onLoadFunction: function (response) {
                        greeting_element.value = '';
                        if (greeting_id) {
                            var greeting_list_element = $('js-greeting_' + greeting_id);
                            greeting_list_element.getElement('.b-blog_controls_greeting_body').innerHTML = response.greeting.body;
                            greeting_list_element.removeClass('b-blog_controls_edit_greeting');
                        } else {
                            var new_greeting_element = new Element('li', {
                                html: '<a class="b-blog_controls_greeting_delete" onclick="var e = new Event(event); e.preventDefault(); blogsSettingsHandler.deleteGreeting(\'{greeting_id}\');" href="#" class="b-fui_icon_button b-fui_icon_button_delete"><span></span></a>\
								<input class="i-form_text_input" type="text" name="name" value="" onkeyup="blogsSettingsHandler.saveGreeting(event, this, \'{greeting_id}\');">\
								<span class="b-blog_controls_greeting_body">{greting_body}</span>'.substitute({
                                        greeting_id: response.greeting.id,
                                        greting_body: response.greeting.body
                                    }),
                                id: 'js-greeting_' + response.greeting.id,
                                events: {
                                    'click': function (e) {
                                        e = new Event(e);
                                        e.stopPropagation();
                                        blogsSettingsHandler.editGreeting(response.greeting.id);
                                    }
                                }
                            });
                            new_greeting_element.inject($('js-blog_controls_greetings_list'), 'top');
                        }
                    }
                });
            }
        } else if (e && e.keyCode == 27) {
            if (greeting_id) {
                var greeting_list_element = $('js-greeting_' + greeting_id);
                greeting_list_element.removeClass('b-blog_controls_edit_greeting');
            }
        }
        if (greeting_element.value.trim().length > 0) {
            $$('.b-header_tagline_body')[0].innerHTML = greeting_element.value;
        } else {
            $$('.b-header_tagline_body')[0].innerHTML = '&nbsp;';
        }
    },
    saveAllActiveGreetings: function () {
        $(document.body).removeEvent('click', blogsSettingsHandler.saveAllActiveGreetings);
        var active_greeting_elements = $A($('js-blog_controls_greetings_list').getElements('.b-blog_controls_edit_greeting'));
        active_greeting_elements.each(function (active_greeting_element) {
            blogsSettingsHandler.saveGreeting(false, active_greeting_element.getElement('.i-form_text_input'), active_greeting_element.id.substr(12));
        });
    },
    editGreeting: function (greeting_id) {
        var greeting_list_element = $('js-greeting_' + greeting_id);
        if (!greeting_list_element.hasClass('b-blog_controls_edit_greeting')) {
            blogsSettingsHandler.saveAllActiveGreetings();

            var greeting_input_element = greeting_list_element.getElement('.i-form_text_input');
            var greeting_body = greeting_list_element.getElement('.b-blog_controls_greeting_body').innerHTML;

            greeting_input_element.value = greeting_body;
            greeting_list_element.addClass('b-blog_controls_edit_greeting');

            greeting_input_element.focus();
            greeting_input_element.select();

            $$('.b-header_tagline_body')[0].innerHTML = greeting_body;

            (function () {
                $(document.body).addEvent('click', blogsSettingsHandler.saveAllActiveGreetings);
            }).delay(200);
        }
    },
    deleteGreeting: function (greeting_id) {
        var greeting_list_element = $('js-greeting_' + greeting_id);
        var data = 'id=' + greeting_id;
        new futuAjax({
            button: greeting_list_element,
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.greetings_delete,
            data: data,
            onLoadFunction: function (response) {
                greeting_list_element.set('morph', {
                    duration: 222,
                    onComplete: function () {
                        greeting_list_element.destroy();
                    }
                });
                greeting_list_element.morph({
                    height: 0,
                    marginBottom: 0
                });
            }
        });
    },
    showUsersList: function (user_list) {
        var containers = ['mod', 'ban'];
        var user_lists = ['ban'];
        var user_list_element = $('js-blog_controls_' + user_list);
        user_list_element.getElements('form, ul').toggleClass('hidden');
        if (user_list_element.getElement('.b-blog_controls_users_all')) {
            user_list_element.getElement('.b-blog_controls_users_all').toggleClass('hidden');
        }
        if (!user_list_element.getElement('ul').hasClass('hidden') && !user_list_element.getElement('li')) {
            blogsSettingsHandler.loadUsersList(user_list);
        }
        for (var i = 0; i < user_lists.length; i++) {
            if ($('js-blog_controls_' + user_lists[i]) && user_list != user_lists[i]) {
                var users_list_item_element_item = $('js-blog_controls_' + containers[i]);
                users_list_item_element_item.getElements('form, ul').addClass('hidden');
                if (users_list_item_element_item.getElement('.b-blog_controls_users_all')) {
                    users_list_item_element_item.getElement('.b-blog_controls_users_all').addClass('hidden');
                }
            }
        }
    },
    loadUsersList: function (user_list, clear) {
        var user_list_element = $('js-blog_controls_' + user_list);
        var data = 'list=' + user_list;
        data += '&domain=' + globals.domain.id;
        new futuAjax({
            button: user_list_element.getElement('label'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.controls_acl,
            data: data,
            onLoadFunction: function (response) {
                var iHTML = '';
                var ul = user_list_element.getElement('ul');
                if (clear) {
                    ul.empty();
                }
                for (var i = 0; i < response.users.length; i++) {
                    var user = response.users[i];
                    var user_element = new Element('li', {
                        id: 'js-user_' + user_list + '_' + user.id,
                        html: '<a class="b-fui_icon_button b-fui_icon_button_close" href="#" onclick="blogsSettingsHandler.expellUsersFromList(\'{user_list}\', \'{user_id}\'); return false;"><span></span><em></em></a><a href="http://{base_domain_url}/user/{user_login}" target="_blank">{user_login}</a>'.substitute({
                            base_domain_url: document.base_domain_url,
                            user_login: user.login,
                            user_id: user.id,
                            user_list: user_list
                        }),
                        'class': ((globals.user.id == user.id) || (globals.domain_owner && globals.domain_owner.id == user.id)) && (user_list == 'readers' || user_list == 'writers') ? 'b-user_owner' : ''
                    });
                    user_element.inject(ul);
                }
            }
        });
    },
    expelPrime: function (button) {
        var prime_element = $('js-blog_controls_prime');
        var prime_id_element = $('js-blog_controls_prime_id');

        var data = 'user_ids=' + prime_id_element.value;
        new futuAjax({
            button: button,
            attribute: 'color',
            color_to: Colors.text_color,
            color_from: Colors.links_color,
            url: ajaxUrls.controls_prime_remove,
            data: data,
            onLoadFunction: function (response) {
                prime_element.getElement('.b-blog_controls_prime_new').removeClass('hidden');
                prime_element.getElement('.b-blog_controls_prime_current').addClass('hidden');
                prime_id_element.value = '';
                new futuAlert('Премьер-министр уволен.');
            }
        });
    },
    validatePrime: function () {
        var prime_element = $('js-blog_controls_prime');
        var prime_call_element = prime_element.getElement('input[name="call"]');
        if (prime_call_element.value.trim().length == 0) {
            ajaxHandler.highlightField(prime_call_element);
            return false;
        }
        return true;
    },
    setPrime: function () {
        if (blogsSettingsHandler.validatePrime()) {
            var prime_element = $('js-blog_controls_prime');
            var prime_id_element = $('js-blog_controls_prime_id');
            var prime_call_element = prime_element.getElement('input[name="call"]');

            /*var data = 'list=primes&call=' + prime_call_element.value;
             data += '&domain=' + globals.domain.id;*/
            var data = 'users=' + prime_call_element.value;
            new futuAjax({
                button: prime_element.getElement('.b-blog_controls_prime_new .b-fui_icon_button'),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.controls_prime_add,
                data: data,
                onLoadFunction: function (response) {
                    var prime_user = response.access_settings.prime[0];
                    prime_call_element.value = '';
                    prime_id_element.value = prime_user.id;
                    prime_element.getElement('.b-blog_controls_prime_current_name').href = globals.base_domain = +'/user/' + prime_user.login;
                    prime_element.getElement('.b-blog_controls_prime_current_name').innerHTML = prime_user.login;
                    prime_element.getElement('.b-blog_controls_prime_new').addClass('hidden');
                    prime_element.getElement('.b-blog_controls_prime_current').removeClass('hidden');

                    if ($('js-user_mod_' + prime_user.id)) {
                        $('js-user_mod_' + prime_user.id).destroy();
                    }
                }
            });
        }
    },
    validateAddUsersToList: function (user_list) {
        var user_list_element = $('js-blog_controls_' + user_list);
        var user_list_form_element = user_list_element.getElement('form');
        var user_list_call_element = user_list_form_element.getElement('input[name="call"]');
        if (user_list_call_element.value.trim().length == 0) {
            ajaxHandler.highlightField(user_list_call_element);
            return false;
        }
        return true;
    },
    addUsersToList: function (user_list) {
        var user_list_element = $('js-blog_controls_' + user_list);
        var user_list_form_element = user_list_element.getElement('form');
        var user_list_call_element = user_list_form_element.getElement('input[name="call"]');
        var reason = user_list_form_element.getElement('textarea[name="reason"]');
        var duration = user_list_form_element.getElement('input[name="duration"]');
        if (blogsSettingsHandler.validateAddUsersToList(user_list)) {
            var url,
                data;
            switch (user_list) {
                case 'mod':
                    url = ajaxUrls.controls_moderators_add;
                    data = 'users=' + user_list_call_element.value.trim();
                    break;
                case 'ban':
                    url = ajaxUrls.user_ban;
                    data = 'users=' + user_list_call_element.value + '&reason=' + reason.value + '&duration=' + duration.value;
                    break;
                case 'read':
                case 'post':
                case 'comment':
                case 'vote':
                    url = ajaxUrls.controls_acl_add;
                    data = 'list_name=' + user_list + '&users=' + user_list_call_element.value.trim();
                    break;
                default:
                    url = ajaxUrls.controls_acl;
                    data = user_list_form_element.toQueryString() + '&list=' + user_list;
            }
            new futuAjax({
                button: user_list_form_element.getElement('.b-fui_icon_button'),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: url,
                data: data,
                onLoadFunction: function (response) {
                    switch (user_list) {
                        case 'mod':
                            blogsSettingsHandler.addUserItems(user_list_element, response.access_settings.moderators, user_list);
                            break;
                        case 'ban':
                            blogsSettingsHandler.addUserItems(user_list_element, response.banned_users, user_list, response.bans);
                            break;
                        case 'read':
                        case 'post':
                        case 'comment':
                        case 'vote':
                            for (var i in response.access_settings) {
                                if (response.access_settings.hasOwnProperty(i)) {
                                    if (['read', 'post', 'comment', 'vote'].indexOf(i) > -1) {
                                        user_list_element = $('js-blog_controls_' + i);
                                        blogsSettingsHandler.addUserItems(user_list_element, response.access_settings[i].members, i);
                                    }
                                }
                            }
                            break;
                    }
                    /*if (response.access_settings) {
                     for (var i in response.access_settings) {
                     if (response.access_settings.hasOwnProperty(i)) {
                     user_list_element = $('js-blog_controls_' + i);
                     blogsSettingsHandler.addUserItems(user_list_element, response.access_settings[i].members, i);
                     }
                     }
                     } else {
                     if (response.banned_users) {
                     response.users = response.banned_users;
                     } else if (response.members) {
                     response.users = response.members;
                     }
                     blogsSettingsHandler.addUserItems(user_list_element, response.users, user_list, response.bans);
                     }*/
                    user_list_call_element.value = '';

                    if (reason) {
                        reason.value = '';
                    }
                    if (duration) {
                        duration.value = '';
                        datesHandler.setDates();
                    }
                }
            });
        }
    },
    addUserItems: function (user_list_element, users, user_list, bans) {
        var ul_element = user_list_element.getElement('ul');
        for (var i = 0; i < users.length; i++) {
            var user = users[i];
            if (!$('js-user_' + user_list + '_' + user.id)) {
                var description = '';

                if (user_list == 'ban' && bans) {
                    if (bans[i].reason) {
                        description += '<span class="b-sys_text">&mdash; ' + bans[i].reason + '</span> ';
                    }
                    if (bans[i].expires) {
                        description += '<span class="b-form_field_description b-ban_duration">до <span data-epoch_date="' + bans[i].expires + '" class="js-date js-date-regular-date-time">' + bans[i].expires + '</span></span>';
                    }
                }

                var user_element = new Element('li', {
                    id: 'js-user_' + user_list + '_' + user.id,
                    html: '<a href="{base_domain_url}/user/{user_login}" target="_blank">{user_login}</a> {description}'.substitute({
                        base_domain_url: globals.base_domain.url,
                        user_login: user.login,
                        user_id: user.id,
                        user_list: user_list,
                        description: description
                    })
                });
                if (user.is_expelable) {
                    var user_remove_element = new Element('a', {
                        'class': 'b-fui_icon_button b-fui_icon_button_close',
                        href: '#',
                        onclick: 'blogsSettingsHandler.expellUsersFromList(\'{user_list}\', \'{user_id}\'); return false;'.substitute({
                            user_id: user.id,
                            user_list: user_list
                        }),
                        html: '<span></span><em></em>'
                    });
                    user_remove_element.inject(user_element, 'top');
                }
                user_element.inject(ul_element);
            }
        }
    },
    expellUsersFromList: function (user_list, user_id) {
        var url,
            data;

        switch (user_list) {
            case 'mod':
                url = ajaxUrls.controls_moderators_remove;
                data = 'user_ids=' + user_id;
                break;
            case 'ban':
                url = ajaxUrls.user_ban_delete;
                data = 'users=' + encodeURIComponent($('js-user_' + user_list + '_' + user_id).getElements('a')[1].innerHTML);
                break;
            case 'read':
            case 'post':
            case 'comment':
            case 'vote':
                url = ajaxUrls.controls_acl_remove;
                data = 'list_name=' + user_list + '&user_ids=' + user_id;
                break;
            default:
                url = ajaxUrls.controls_acl;
                data = 'expel=' + user_id + '&list=' + user_list;
        }

        new futuAjax({
            button: $('js-user_' + user_list + '_' + user_id),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: url,
            data: data,
            onLoadFunction: function (response) {
                switch (user_list) {
                    case 'read':
                    case 'post':
                    case 'comment':
                    case 'vote':
                        for (var i in response.access_settings) {
                            if (response.access_settings.hasOwnProperty(i)) {
                                if (['read', 'post', 'comment', 'vote'].indexOf(i) > -1) {
                                    blogsSettingsHandler.removeUserItems(response.access_settings[i].members, i);
                                }
                            }
                        }
                        break;
                    default:
                        $('js-user_' + user_list + '_' + user_id).destroy();
                }


                /*if (response.access_settings) {
                 for (var i in response.access_settings) {
                 if (response.access_settings.hasOwnProperty(i)) {
                 if (['read', 'post', 'comment', 'vote'].indexOf(i) > -1) {
                 blogsSettingsHandler.removeUserItems(response.access_settings[i].members, i);
                 }
                 }
                 }
                 } else {
                 $('js-user_' + user_list + '_' + user_id).destroy();
                 }*/
            }
        });
    },
    removeUserItems: function (users, user_list) {
        var user_list_element = $('js-blog_controls_' + user_list),
            user_items,
            user_item_id,
            remove_user;

        if (user_list_element) {
            user_items = user_list_element.getElements('li');
            user_items.each(function (item) {
                remove_user = true;
                for (var i = 0, l = users.length; i < l; i++) {
                    user_item_id = item.get('id').replace('js-user_' + user_list + '_', '');
                    if (user_item_id == users[i].id) {
                        remove_user = false;
                    }
                }
                if (remove_user) {
                    item.destroy();
                }
            })
        }
    },
    limitsFormInit: function () {
        var form_containers = $$('.b-controls_limits_form'),
            checkboxes,
            text_fields,
            date_field,
            date_link_el,
            form;

        form_containers.each(function (container, i) {
            checkboxes = container.getElements('input[type="checkbox"]');
            text_fields = container.getElements('input[type="text"]');
            form = container.getElement('form');
            date_field = $('js-' + form.get('data-section_name') + '_registration_date_lt');
            date_link_el = new Element('span', {
                'class': 'b-limits_date',
                html: '<span class="b-sys_link b-open_link">' + date_field.value + '</span>'
            });
            date_link_el.inject(date_field, 'after');
            date_field.addClass('hidden');
            date_field.set('data-meiomask', 'fixed.date');
            date_field.meiomask(date_field.get('data-meiomask'));

            checkboxes.addEvent('change', function (event) {
                blogsSettingsHandler.setLimits(event);
            });
            text_fields.addEvent('keydown', (function (event) {
                if (event.code == 13) {
                    event.preventDefault();
                    blogsSettingsHandler.setLimits(event);
                }
            }).bind(this));

            text_fields.addEvent('blur', (function (event) {
                blogsSettingsHandler.setLimits(event);
            }).bind(this));

            // При клике по ссылке с датой показываем поле ввода
            date_link_el.getElement('.b-open_link').addEvent('click', function (event) {
                event.preventDefault();
                var target = event.target,
                    parent = target.getParent('.b-limits_date'),
                    date_element = parent.getSiblings('.i-form_text_input')[0];

                date_element.removeClass('hidden');
                parent.addClass('hidden');
                date_element.focus();
            });


            // При потере фокуса меняем дату на нужный формат
            date_field.addEvent('blur', function (event) {
                var target = event.target,
                    date_element = target.getSiblings('.b-limits_date')[0];

                date_element.getElement('.b-open_link').innerHTML = target.value;
                date_element.removeClass('hidden');
                target.addClass('hidden');
                blogsSettingsHandler.setLimits(event);
            });
        });
    },
    updateAccessForms: function (access_settings) {
        var user_list_element;
        for (var i in access_settings) {
            if (access_settings.hasOwnProperty(i)) {
                if (['read', 'post', 'comment', 'vote'].indexOf(i) > -1) {
                    $('js-controls_access_' + i + '_' + access_settings[i].type + '_input').set('checked', true);
                    $('js-controls_' + i + '_limits_form').innerHTML = access_settings[i].discrimination_form_template;
                    blogsSettingsHandler.toggleAccessClass(i, access_settings[i].type);
                    iconsHandler.update();
                    user_list_element = $('js-blog_controls_' + i);
                    blogsSettingsHandler.removeUserItems(access_settings[i].members, i);
                    blogsSettingsHandler.addUserItems(user_list_element, access_settings[i].members, i);
                }
            }
        }
    },
    setLimits: function (event) {
        var target = event.target,
            form = target.getParent('form'),
            inputs = form.getElements('input'),
            data = {};

        inputs.each(function (input) {
            if (input.type == 'text') {
                data[input.name] = input.value;
            } else if (input.type == 'checkbox') {
                if (input.get('checked')) {
                    data[input.name] = 'True';
                } else {
                    data[input.name] = 'False';
                }
            }
        });
        data = Object.toQueryString(data);

        new futuAjax({
            button: target,
            attribute: 'opacity',
            color_to: 1,
            color_from: 0.5,
            url: ajaxUrls.controls_discrimination,
            data: data,
            onLoadFunction: function (response) {
                blogsSettingsHandler.updateAccessForms(response.access_settings);
                blogsSettingsHandler.limitsFormInit();
                //new futuAlert('Ограничения сохранены.', false, '', false);
            }
        });
    },
    setAccess: function (name, type) {
        var target_el = $('js-controls_access_' + name + '_' + type + '_input');

        blogsSettingsHandler.toggleAccessClass(name, type);
        var data = 'list_name=' + name + '&access_type=' + type;

        new futuAjax({
            button: target_el,
            attribute: 'opacity',
            color_to: 1,
            color_from: 1,
            url: ajaxUrls.controls_access,
            data: data,
            onLoadFunction: function (response) {
                blogsSettingsHandler.updateAccessForms(response.access_settings);
                blogsSettingsHandler.limitsFormInit();
                blogsSettingsHandler.toggleAccessRadioButtons();
                //new futuAlert('Ограничения сохранены.', false, '', false);
            }
        });
        /*var postLimit = $('js-post_karma_limits').value,
         commentLimit = $('js-comment_karma_limits').value,
         data = 'karma_for_post=' + postLimit + '&karma_for_comment=' + commentLimit;

         data += '&domain=' + globals.domain.id;

         new futuAjax({
         button : $('js-karma_limits_form'),
         attribute : 'opacity',
         color_to : 1,
         color_from : 1,
         url : ajaxUrls.controls_edit,
         data : data,
         onLoadFunction : function (response) {
         new futuAlert('Ограничения сохранены.');
         }
         });*/
    },
    toggleAccessClass: function (name, type) {
        var access_limit_type_element = $('js-controls_access_' + name + '_' + type);
        var access_section_element = access_limit_type_element.getParent('.b-blog_controls_users');

        access_section_element.getElements('.b-controls_access_limits_type').
            removeClass('b-controls_access_limits_type_active');
        if (type != 'public') {
            access_limit_type_element.addClass('b-controls_access_limits_type_active');
        }
    },
    resetKarmaLimits: function (param, value) {
        var data = param + '=' + value;
        var field = $$('.i-form_text_input[name="' + param + '"]')[0];
        data += '&domain=' + globals.domain.id;
        new futuAjax({
            button: field,
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.controls_edit,
            data: data,
            onLoadFunction: function (response) {
                field.value = value;
            }
        });
    },
    removeDomain: function () {
        new futuAjax({
            button: $('js-remove_domain_button'),
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            url: ajaxUrls.controls_delete,
            onLoadFunction: function (response) {
                new futuAlert('Сайт удален.');

                (function () {
                    window.location.href = globals.base_domain ? globals.base_domain.url : '/';
                }).delay(5000);
            }
        });
    },
    // Передача домена
    handOverDomain: function () {
        var sendData = function (password) {
            var container = $('js-change_owner_form'),
                form = $('js-hand_over_domain_form'),
                loginInput = form.getElement('input[name="login"]'),
                login = loginInput.value.trim(),
                button = form.getElement('.i-form_button'),
                futureOwnerLink = container.getElement('.b-future_owner_name_login'),
                data = 'login=' + login;

            if (password) {
                data += '&password=' + encodeURIComponent(password);
            }
            data += '&domain=' + globals.domain.id;
            if (login.length) {
                new futuAjax({
                    button: button,
                    attribute: 'opacity',
                    color_to: 0.5,
                    color_from: 1,
                    url: ajaxUrls.controls_hand_over,
                    data: data,
                    onLoadFunction: function (response) {
                        if (profile_password_confirm) {
                            profile_password_confirm.close();
                        }
                        var msg = 'В инбокс пользователя <a href="' + globals.parent_site + '/user/' + login + '">' + login + '</a> ' + 'отправлена ссылка для принятия сайта. ';
                        container.addClass('b-blog_controls_change_in_process');
                        futureOwnerLink.href = globals.parent_site + '/user/' + login;
                        futureOwnerLink.innerHTML = login;
                        loginInput.value = '';
                        new futuAlert(msg);
                    }
                });
            } else {
                ajaxHandler.highlightField(loginInput);
            }
        }
        var profile_password_confirm = futu_password_confirm('Для передачи домена необходим ваш пароль, пожалуйста:', sendData);
    },
    cancelDomainHandOver: function () {
        var container = $('js-change_owner_form'),
            button = container.getElement('.b-change_owner_cancel_button');

        this.showHiddenControls(container);

        new futuAjax({
            button: button,
            attribute: 'opacity',
            color_to: 0.5,
            color_from: 1,
            data: 'domain=' + globals.domain.id,
            url: ajaxUrls.controls_cancel_hand_over,
            onLoadFunction: function (response) {
                container.removeClass('b-blog_controls_change_in_process');
            }
        });
    },
    // Раскрытие/сворачивание форм управления подсайтом
    showHiddenControls: function (container) {
        var siblings = container.getSiblings();
        var changeOwnerContainer = container.getSiblings('.b-blog_controls_change_in_process')[0];

        container.toggleClass('active', !container.hasClass('active'));

        // Если скрывается контейнер удаления подсайта,
        // а передача домена в процессе,
        // то нужно вернуть активное состояние контенеру передачи домена
        if (container == $('js-remove_domain_container') && !container.hasClass('active') && changeOwnerContainer) {
            changeOwnerContainer.addClass('active');
        } else {
            siblings.removeClass('active');
        }
    },
    elections: {
        toggleElections: function (checkbox) {
            var elections_settings_element = $('js-blog_controls_elections_settings');
            if (checkbox.checked) {
                elections_settings_element.addClass('b-blog_controls_elections_settings_holder_opened');
            } else {
                if (elections_settings_element.hasClass('js-elections_enabled')) {
                    new futuDialogPopup({
                        text: 'Сейчас отключатся выборы, точно?',
                        type: 'confirm',
                        callback: function () {
                            elections_settings_element.removeClass('b-blog_controls_elections_settings_holder_opened');
                            blogsSettingsHandler.elections.disableElections();
                        },
                        cancel: function () {
                            checkbox.checked = true;
                        }
                    });
                } else {
                    elections_settings_element.removeClass('b-blog_controls_elections_settings_holder_opened');
                }
            }
        },
        toggleElectionsSettingsBlock: function (list_name) {
            var element = $('js-blog_controls_elections_settings_' + list_name);
            element.toggleClass('b-blog_controls_elections_settings_block_holder_opened');
        },
        initForm: function () {
            blogsSettingsHandler.elections.setUsersListSummary('candidates');
            blogsSettingsHandler.elections.setUsersListSummary('electorate');
            $A($('js-blog_controls_elections_settings_form').getElements('input, select')).each(function (input) {
                input.addEvent('change', function () {
                    blogsSettingsHandler.elections.setUsersListSummary('candidates');
                    blogsSettingsHandler.elections.setUsersListSummary('electorate');
                });
                input.addEvent('click', function () {
                    blogsSettingsHandler.elections.setUsersListSummary('candidates');
                    blogsSettingsHandler.elections.setUsersListSummary('electorate');
                });
                input.addEvent('keyup', function () {
                    blogsSettingsHandler.elections.setUsersListSummary('candidates');
                    blogsSettingsHandler.elections.setUsersListSummary('electorate');
                });
            });
        },
        setUsersListSummary: function (list_name) {
            var summary_element = $('js-blog_controls_elections_settings_' + list_name + '_summary');
            var elections_settings = $('js-blog_controls_elections_settings_form').toQueryString().parseQueryString();

            var users_action = (list_name == 'electorate') ? 'В выборах участвуют' : 'Предложить свою кандидатуру в президенты <a href="{domain}">{domain_name}</a> могут'.substitute({
                'domain': globals.domain.url,
                'domain_name': globals.domain.url.substr(7),
            });

            var users_gender_code = elections_settings[list_name + '_gender'];
            var users_gender = '';
            if (list_name == 'electorate') {
                users_gender = (users_gender_code == 'male') ? 'только мужчины' :
                    (users_gender_code == 'female') ? 'только женщины' :
                        'все пользователи';
            } else {
                users_gender = (users_gender_code == 'male') ? 'только мужчины' :
                    (users_gender_code == 'female') ? 'только женщины' :
                        'все пользователи';
            }

            var users_karma = '';
            var karma_limit = elections_settings[list_name + '_karma'];
            if (parseInt(karma_limit) || parseInt(karma_limit) === 0) {
                users_karma = (elections_settings[list_name + '_karma_limit'] == 'True') ? 'с кармой выше ' + parseInt(karma_limit) : '';
            } else {
                $('js-blog_controls_elections_settings_' + list_name + '_karma_limit').checked = false;
            }

            var experience_limit_days = elections_settings[list_name + '_experience'];
            var experience_limit = '';
            if (experience_limit_days == '7') {
                experience_limit = 'неделю';
            } else if (experience_limit_days == '30') {
                experience_limit = 'месяц';
            } else if (experience_limit_days == '365') {
                experience_limit = 'год';
            }
            var users_experience = elections_settings[list_name + '_experience_limit'] == 'True' ? 'зарегистрированные ранее, чем ' + experience_limit + ' назад' : '';

            var html_template = (list_name == 'electorate') ?
                '{users_action} {users_gender} <a href="{base_domain}">{base_domain_name}</a>{users_karma}{and}{users_experience}.' :
                '{users_action} {users_gender} <a href="{base_domain}">{base_domain_name}</a>{users_karma}{and}{users_experience}.';

            var html = html_template.substitute({
                'users_action': users_action,
                'users_gender': users_gender,
                'base_domain': globals.base_domain.url,
                'base_domain_name': globals.base_domain.url.substr(7),
                'users_karma': (users_karma && users_experience) ? ',<br>' + users_karma : (users_karma) ? ' ' + users_karma : '',
                'and': (users_karma && users_experience) ? ' и ' : '',
                'users_experience': (users_karma && users_experience) ? users_experience : (users_experience) ? ', ' + users_experience : ''
            });

            summary_element.innerHTML = html;
        },
        saveElectionsSettings: function () {
            var elections_settings_element = $('js-blog_controls_elections_settings');
            var elections_settings_form_element = $('js-blog_controls_elections_settings_form');
            var data = elections_settings_form_element.toQueryString();

            new futuAjax({
                button: elections_settings_form_element.getElement('.b-button_start_elections'),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.elections_settings,
                data: data,
                onLoadFunction: function (response) {
                    if (elections_settings_element.hasClass('js-elections_enabled')) {
                        new futuAlert('Настройки выборов сохранены. Они будут применены к следующим выборам.');
                    } else {
                        new futuAlert('Вы включили выборы! И сейчас вас перенесёт на главную подсайта.');
                        (function () {
                            window.location.href = '/';
                        }).delay(2000);
                        elections_settings_element.addClass('js-elections_enabled');
                    }
                }
            });

        },
        disableElections: function () {
            var elections_settings_form_element = $('js-blog_controls_elections_settings_form');
            var description = $('js-blog_controls_elections_description');
            new futuAjax({
                button: elections_settings_form_element.getElement('.b-button_start_elections'),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.elections_disable,
                data: '',
                onLoadFunction: function (response) {
                    new futuAlert('Выборы отключены.');
                    if ($('js-blog_controls_elections_settings')) {
                        $('js-blog_controls_elections_settings').removeClass('js-elections_enabled');
                    } else {
                        (function () {
                            window.location.href = '/';
                        }).delay(1000);
                    }
                    if (description) {
                        description.removeClass('b-form_field_description__enabled');
                    }
                }
            });
        },
        cancelElections: function () {
            new futuDialogPopup({
                text: 'Сейчас отключатся выборы, и вы опять станете владельцем сайта, уверены?',
                type: 'confirm',
                callback: function () {
                    blogsSettingsHandler.elections.disableElections();
                }
            });
        }
    },
    // Инициализация форм переключения настроек доступа к сайту
    /*domainAccessFormInit: function() {
     var items = $$('.js-toggle_form_item');

     items.each(function(item, i) {
     var inputs = item.getElements('.js-toggle_form_input'),
     activeInput = inputs.filter(':checked')[0];

     // Выделение одного из инпутов, если ни один не был выбран
     if (!activeInput) {
     inputs[0].set('checked', true);
     activeInput = inputs[0];
     }

     this.toggleDomainAccessForm(activeInput, item);
     this.toggleInputActiveState(activeInput, item);

     inputs.addEvent('change', function(event) {
     var input = event.target,
     data = input.get('name') + '=' + input.get('value');

     data += '&domain=' + globals.domain.id;
     if (input.get('name') == 'read') {
     // При включении ограничения на чтение
     // автоматически должно произойти переключение ограничения на запись по списку
     // и задисейблить переключение на запись для всех
     if (input.get('value') == 'protected') {
     $('b-protected_writing').click();
     $('b-public_writing').set('disabled', true);
     } else if (input.get('value') == 'public') {
     $('b-public_writing').set('disabled', false);
     }
     }

     new futuAjax({
     button: input,
     color_to: '0.5',
     color_from: '1',
     attribute: 'opacity',
     url: ajaxUrls.controls_access,
     data: data,
     onLoadFunction : function (response) {
     this.toggleDomainAccessForm(input, item);
     this.toggleInputActiveState(input, item);
     blogsSettingsHandler.loadUsersList('readers', true);
     blogsSettingsHandler.loadUsersList('writers', true);
     }.bind(this)
     });

     }.bind(this));
     }.bind(this));
     },*/

    // Переключение активного класса у контейнера с инпутом, для его выделения
    /*toggleInputActiveState: function(input, item) {
     var inputsContainers = item.getElements('.b-form_radio_btn'),
     inputContainer = input.getParent('.b-form_radio_btn');

     inputsContainers.removeClass('active');
     inputContainer.addClass('active');
     },*/
    // Переключение возможности изменять настройки доступа у всех типов,
    // кроме доступа на чтение в зависимости от выбранной настройки на чтение
    toggleAccessRadioButtons: function () {
        var read_list_discrimination = $('js-controls_access_read_discrimination_input'),
            read_list_input = $('js-controls_access_read_acl_input'),
            inputs = $$('.i-radio_button[name^=limit_][name!=limit_read]');
        if (read_list_input.checked) {
            inputs.each(function (input) {
                if (input.value == 'public' || input.value == 'limit') {
                    input.set('disabled', 'disabled');
                }
            });
        } else if (read_list_discrimination.checked) {
            inputs.each(function (input) {
                if (input.value == 'public') {
                    input.set('disabled', 'disabled');
                } else {
                    input.set('disabled', '');
                }
            });
        } else {
            inputs.each(function (input) {
                if (input.value == 'public' || input.value == 'limit') {
                    input.set('disabled', '');
                }
            });
        }
    },

    // Переключение форм настроек доступа к сайту
    toggleDomainAccessForm: function (input, item) {
        var contentElements = item.getElements('.js-form_toggle_content'),
            hiddenContent = contentElements.filter('[data-access=' + input.get('value') + ']');

        contentElements.addClass('hidden');
        hiddenContent.removeClass('hidden');
    },
    changeDurationLabel: function (input) {
        var daysQty = Math.abs(parseInt(input.value, 10)) || 0;
        var daysQtyLabel = $('js-duration_label');
        if (daysQty) {
            daysQtyLabel.innerHTML = utils.getPlural(daysQty, ['день', 'дня', 'дней']);
        } else {
            daysQtyLabel.innerHTML = '(дней)';
        }
    }
};

commentControlsHandler = {
    showCommentEditForm: function (button, comment_id, futu_controls) {
        var comment_element = $(comment_id);

        if (!comment_element.getElement('.b-comment_edit')) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.getCommentUrl(comment_id),
                data: '',
                onLoadFunction: function (response) {
                    futu_controls.hide();

                    var comment_body_element = comment_element.getElement('.c_body');

                    var mod_window_element = new Element('div', {
                        'html': '',
                        'class': 'b-comment_edit',
                        'styles': {
                            'height': 0,
                            'overflow': 'hidden'
                        }
                    });

                    mod_window_element.innerHTML = response.template;

                    // replace all br tags with line breaks
                    mod_window_element.getElement('textarea[name="body"]').value = mod_window_element.getElement('textarea[name="body"]').value.replace(/<br>/gi, '\n');

                    mod_window_element.inject(comment_body_element, 'after');
                    comment_body_element.addClass('hidden');

                    mod_window_element.set('morph', {
                        duration: 222,
                        onComplete: function () {
                            mod_window_element.style.height = 'auto';
                            mod_window_element.getElement('.i-form_textarea').focus();
                        }
                    });
                    var mod_window_height = mod_window_element.getElement('form').offsetHeight;
                    mod_window_element.morph({height: mod_window_height});

                    new wysiwyg(mod_window_element.getElement('.b-textarea_editor'), mod_window_element.getElement('.i-form_textarea'), response.user_is_moderator);

                    mod_window_element.getElement('.i-form_textarea').addEvent('keydown', (function (e) {
                        if ((e.meta || e.control) && e.code == 13) {
                            var e = new Event(e);
                            e.preventDefault();
                            commentControlsHandler.saveComment(comment_id);
                            animatePosts.scrollTo(comment_element);
                        }
                    }).bind(this));
                }
            });
        } else {
            futu_controls.hide();
        }
    },
    removeCommentEditForm: function (comment_id) {
        var comment_element = $(comment_id);
        var comment_body_element = comment_element.getElement('.c_body');

        comment_element.getElement('.b-comment_edit').destroy();

        comment_body_element.removeClass('hidden');
    },
    saveComment: function (comment_id) {
        var comment_element = $(comment_id);
        var comment_body_element = comment_element.getElement('.c_body');

        var data = $('js-mod_form_comment_' + comment_id).toQueryString();

        new futuAjax({
            button: comment_element.getElement('.b-post_edit_submit'),
            color_to: Colors.links_color,
            color_from: Colors.background_color,
            url: ajaxUrls.comment_edit,
            data: data,
            onLoadFunction: function (response) {
                comment_body_element.innerHTML = response.comment.body;
                futuPics.initExpandingPics();
                commentControlsHandler.removeCommentEditForm(comment_id);
            }
        });
    },
    showPanel: function (params) {
        params = params || {};
        if (!params.button) {
            new futuAlert('commentControlsHandler требует кнопку.');
            return false;
        }
        params.options = params.options || [];

        params.class_name = params.class_name || '';
        params.close_button_class = params.close_button_class || 'b-post_controls_close_active';
        params.onClose = params.onClose || (function () {
        });

        var moderateDeleteComment = function (button) {
            new futuDialogPopup({
                text: 'Комментарий удалится навсегда, точно?',
                type: 'confirm',
                callback: function () {
                    var comment_element = $(params.comment_id);
                    if (comment_element) {
                        $(comment_element).addClass('js-comment_deleted');
                    }
                    new futuAjax({
                        button: $(button),
                        attribute: 'opacity',
                        color_to: 0.5,
                        color_from: 1,
                        url: ajaxUrls.comment_delete,
                        data: 'id=' + params.comment_id,
                        onLoadFunction: function (response) {
                            new futuAlert('Комментарий удалён.');
                            if (comment_element) {
                                if ($(comment_element).hasClass('js-comment_user_banned')) {
                                    futu_controls.hide();
                                    commentControlsHandler.removeComment(comment_element);
                                } else {
                                    futu_controls.onClose = function () {
                                        commentControlsHandler.removeComment(comment_element);
                                    };
                                }
                            }
                        }
                    });
                }
            });
        };
        var moderateDeleteCommentsBranch = function (button) {
            new futuDialogPopup({
                text: 'Ветка комментариев удалится навсегда, точно?',
                type: 'confirm',
                callback: function () {
                    var comment_element = $(params.comment_id);
                    if (comment_element) {
                        $(comment_element).addClass('js-comment_deleted');
                    }
                    new futuAjax({
                        button: $(button),
                        attribute: 'opacity',
                        color_to: 0.5,
                        color_from: 1,
                        url: ajaxUrls.comment_delete,
                        data: 'id=' + params.comment_id + '&cascade=1',
                        onLoadFunction: function (response) {
                            new futuAlert('Ветка удалена.');
                            if (comment_element) {
                                if ($(comment_element).hasClass('js-comment_user_banned')) {
                                    futu_controls.hide();
                                    commentControlsHandler.removeCommentsBranch(response.deleted_ids);
                                } else {
                                    futu_controls.onClose = function () {
                                        commentControlsHandler.removeCommentsBranch(response.deleted_ids);
                                    };
                                }
                            }
                        }
                    });
                }
            });
        };
        var showBanForm = function (listItem) {
            var initialCoords = listItem.getCoordinates(),
                finalCoords,
                domain = '',
                form,
                formStyle,
                marginRight,
                deleteTitle,
                banForm,
                container;

            if (globals.base_domain) {
                domain = ' на ' + globals.domain.url.substr(7);
            }
            deleteTitle = 'Удалить все посты и комментарии ' + params.user_login + domain;
            banForm = '<form action="#" class="b-futu_controls_form"> <span class="b-futu_controls_label_wrap"><label class="b-futu_controls_form_label">забанить ' + params.user_login + ' на</label> ' +
                '<span class="b-form_input_container"><input class="i-form_text_input i-form_text_input__short" name="duration" type="text" onkeyup="blogsSettingsHandler.changeDurationLabel(this);" />' +
                '&nbsp;<label class="b-input_label" id="js-duration_label">(дней)</label></span></span>' +
                '<textarea class="i-form_textarea i-form_textarea__short" name="reason" placeholder="причина"></textarea>' +
                '<input type="checkbox" class="i-chbx" name="delete_user_docs" id="b-delete_user_docs" title="' + deleteTitle + '"/><label for="b-delete_user_docs" title="' + deleteTitle + '" class="b-chbx_label">удалить всё</label>' +
                '<a href="#" class="b-fui_icon_button b-fui_icon_button__submit" id="js-futu_controls_form_submit"><i>OK</i><em></em></a>' +
                '</form>';

            container = new Element('div', {
                'class': 'b-futu_controls_form_container',
                html: banForm
            });
            listItem.empty();
            container.inject(listItem);
            form = container.getElement('.b-futu_controls_form'),
                finalCoords = form.getCoordinates();
            futu_controls.holder_element.setStyle('max-height', 'none');
            container.setStyles({
                width: initialCoords.width,
                height: initialCoords.height
            });

            formStyle = form.currentStyle || window.getComputedStyle(form);
            marginRight = parseInt(formStyle.marginRight, 10);
            new Fx.Morph(container, {
                duration: 222,
                onComplete: function () {
                    container.addClass('opened');
                    container.erase('style');
                }
            }).start({
                    height: finalCoords.height,
                    width: initialCoords.width < finalCoords.width + marginRight ? finalCoords.width + marginRight : 'auto'
                });
            form.addEvent('submit', function (event) {
                var e = new Event(event);
                e.preventDefault();
                moderateBanUser();
            });
            form.getElement('.b-fui_icon_button__submit').addEvent('click', function (event) {
                var e = new Event(event);
                e.preventDefault();
                moderateBanUser();
            });
        };
        var moderateBanUser = function () {
            var form = $$('.b-futu_controls_form')[0],
                button = $('js-futu_controls_form_submit'),
                durationEl = form.getElement('input[name="duration"]'),
                duration = durationEl ? parseInt(durationEl.value.trim(), 10) : 0,
                reasonEl = form.getElement('textarea[name="reason"]'),
                deleteAllEl = form.getElement('input[name="delete_user_docs"]'),
                genderPronoun = params.user_gender && params.user_gender == 'female' ? 'её' : 'его',
                text = 'Вы уверены, что хотите забанить ' + params.user_login,
                domain = params.domain ? '&domain=' + params.domain : '',
                data = 'users=' + params.user_login + domain;

            if (deleteAllEl.checked) {
                text += ' и удалить все ' + genderPronoun + ' посты и комментарии?'
                data += '&delete_user_docs=true';
            } else {
                text += '?';
            }
            if (duration > 0) {
                data += '&duration=' + duration;
            }
            if (reasonEl) {
                data += '&reason=' + reasonEl.value.trim();
            }

            new futuDialogPopup({
                text: text,
                type: 'confirm',
                callback: function () {
                    var comment_element = $(params.comment_id);
                    if (comment_element) {
                        $(comment_element).addClass('js-post_user_banned');
                    }
                    new futuAjax({
                        button: button,
                        attribute: 'opacity',
                        color_to: 0.5,
                        color_from: 1,
                        url: ajaxUrls.user_ban,
                        data: data,
                        onLoadFunction: function (response) {
                            var message,
                                comments;

                            if (response.user_docs_deleted) {
                                comments = $$('.comment[data-user_id=' + params.user_id + ']');
                                message = 'Автор забанен, его посты и комментарии будут удалены через некоторое время.';
                                futu_controls.hide();
                                comments.each(function (comment, i) {
                                    if (comment.get('data-domain_id') == params.domain) {
                                        commentControlsHandler.removeComment(comment);
                                    }
                                });
                            } else {
                                message = 'Автор забанен.';
                                if (comment_element) {
                                    if ($(comment_element).hasClass('js-comment_deleted')) {
                                        futu_controls.hide();
                                        commentControlsHandler.removeComment(comment_element);
                                    }
                                }
                            }
                            new futuAlert(message);
                        }
                    });
                }
            });
        };
        var userIgnore = function (button) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.users_ignore,
                data: 'users=' + params.user_login,
                onLoadFunction: function (response) {
                    var userLinks = $('js-comments').getElements('.c_user[data-user_id="' + params.user_id + '"]'),
                        k = 0,
                        comments = {}, // Список комментариев, которые нужно скрыть
                        comment,
                        currentLevel,
                        level,
                        siblingsComments;

                    new futuAlert('Вы больше не увидите пользователя ' + params.user_login + '.');
                    futu_controls.hide();

                    for (var i = 0, l = userLinks.length; i < l; i++) {
                        comment = userLinks[i].getParent('.comment');

                        if (comment) {
                            comments[comment.id] = comment; // Добавление в comments комментария, в котором было раскрыто меню игнорирования пользователя
                            siblingsComments = comment.getAllNext('.comment');
                            currentLevel = comment.className.match(/indent_(\d+)/);

                            // Нахождение и добавление в comments всех вложенных комментариев —
                            // следующие соседние элементы с уровнем больше текущего
                            if (currentLevel[1]) {
                                for (var j = 0, jl = siblingsComments.length; j < jl; j++) {
                                    level = siblingsComments[j].className.match(/indent_(\d+)/);

                                    if (level && typeof parseInt(level[1], 10) === 'number' && level[1] > currentLevel[1]) {
                                        comments[siblingsComments[j].id] = siblingsComments[j];
                                    } else {
                                        break;
                                    }
                                }
                            }
                        }
                    }

                    for (i in comments) {
                        if (comments.hasOwnProperty(i)) {
                            commentControlsHandler.removeComment(comments[i]);
                            k++;
                        }
                    }

                    if (k > 0) {
                        var ignoredDescriptionEl = $('js-comments').getElement('.b-comments_ignored_description'),
                            currentCount,
                            currentCountValue;

                        if (ignoredDescriptionEl) {
                            currentCount = ignoredDescriptionEl.getElement('.b-comments_ignored_description_count'),
                                currentCountValue = currentCount ? parseInt(currentCount.innerHTML, 10) : 0;

                            ignoredDescriptionEl.innerHTML = '(Обсуждение содержит <span class="b-comments_ignored_description_count">' + (k + currentCountValue) + '</span> ' + utils.getPlural(k + currentCountValue, ['скрытый комментарий', 'скрытых комментария', 'скрытых комментариев']) + ')';
                        }
                    }
                }
            });
        };
        commentEdit = function (button) {
            commentControlsHandler.showCommentEditForm(button, params.comment_id, futu_controls);
        };

        var possible_options = {
            'moderate_delete_comment': {
                caption: 'удалить комментарий',
                onclick: moderateDeleteComment,
                ontouchend: moderateDeleteComment
            },
            'moderate_delete_comments_branch': {
                caption: 'удалить ветку комментариев',
                onclick: moderateDeleteCommentsBranch,
                ontouchend: moderateDeleteCommentsBranch
            },
            'moderate_ban_user': {
                caption: 'забанить ' + params.user_login,
                onclick: showBanForm,
                ontouchend: showBanForm
            },
            'user_ignore': {
                caption: 'игнорировать ' + params.user_login,
                onclick: userIgnore,
                ontouchend: userIgnore
            },
            'comment_edit': {
                caption: 'редактировать',
                onclick: commentEdit,
                ontouchend: commentEdit
            }
        };
        var options = [];
        var default_options = [
            {
                caption: 'не показывать пост',
                onclick: function (button) {
                    console.log(this, button, post_id);
                }
            },
            {
                caption: 'не показывать автора',
                onclick: function (button) {
                    console.log(this, button, post_id);
                }
            },
            {
                caption: 'отписаться от сайта',
                onclick: function (button) {
                    console.log(this, button, post_id);
                }
            }
        ];

        if (params.options.length > 0) {
            for (var i = 0; i < params.options.length; i++) {
                var option = possible_options[params.options[i]];
                if (option) {
                    options.push(option);
                }
                if (params.options[i] == 'moderate_ban_user' || params.options[i] == 'moderate_ban_and_delete' || params.options[i] == 'moderate_delete_comment') {
                    if (params.class_name.indexOf('b-futu_controls_moderate') < 0) {
                        params.class_name += ' b-futu_controls_moderate';
                    }
                    params.close_button_class = 'b-post_controls_moderate_active';
                }
            }
        } else {
            options = default_options;
        }

        var futu_controls = new futuControls({
            button: params.button,
            close_button_class: params.close_button_class,
            onClose: params.onClose,
            options: options,
            class_name: params.class_name
        });
    },
    deleteComment: function (button, comment_id) {
        new futuDialogPopup({
            text: 'Комментарий удалится навсегда, точно?',
            type: 'confirm',
            callback: function () {
                var comment_element = $(comment_id);
                new futuAjax({
                    button: $(button),
                    attribute: 'opacity',
                    color_to: 0.5,
                    color_from: 1,
                    url: ajaxUrls.comment_delete,
                    data: 'id=' + comment_id,
                    onLoadFunction: function (response) {
                        new futuAlert('Комментарий удалён.');
                        commentControlsHandler.removeComment(comment_element);
                    }
                });
            }
        });
    },
    removeComment: function (comment_element) {
        comment_element.set('morph', {duration: 333, onComplete: function () {
            comment_element.destroy();
        }});
        comment_element.style.overflow = 'hidden';
        comment_element.morph({'height': 0, 'paddingBottom': 0, 'paddingTop': 0});
    },
    removeCommentsBranch: function (comments_ids) {
        for (var i = 0; i < comments_ids.length; i++) {
            var comment_element = $('' + comments_ids[i]);
            commentControlsHandler.removeComment(comment_element);
        }
    }
};
postControlsHandler = {
    showPanel: function (params) {
        params = params || {};
        if (!params.button) {
            new futuAlert('postControlsHandler требует кнопку.');
            return false;
        }
        params.options = params.options || [];

        params.class_name = params.class_name || '';
        params.close_button_class = params.close_button_class || 'b-post_controls_close_active';
        params.onClose = params.onClose || (function () {
        });

        var favouritesDelete = function (button) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.favourites_out,
                data: 'post=' + params.post_id,
                onLoadFunction: function (response) {
                    futu_controls.hide();
                    var post = $('p' + params.post_id);
                    postControlsHandler.removePost(post);
                }
            });
        };
        var inboxDelete = function (button) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.inbox_delete,
                data: 'post=' + params.post_id,
                onLoadFunction: function (response) {
                    futu_controls.hide();
                    var post = $('p' + params.post_id);
                    postControlsHandler.removePost(post);

                    var unread_count_caption = moreHandler.getUnreadCounterCaption(response.unread_count);
                    var icon_button = $$('.b-fui_icon_button_inbox')[0];
                    icon_button.getElement('.b-header_nav_count').innerHTML = unread_count_caption ? ' <u>' + unread_count_caption + '</u>' : '';
                    if (!unread_count_caption) {
                        icon_button.removeClass('b-button__invert');
                    }
                }
            });
        };
        var myThingsDelete = function (button) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.interest_out,
                data: 'post=' + params.post_id,
                onLoadFunction: function (response) {
                    futu_controls.hide();
                    var post = $('p' + params.post_id);
                    postControlsHandler.removePost(post);

                    var unread_count_caption = moreHandler.getUnreadCounterCaption(response.unread_count);
                    $$('.b-fui_icon_button_interest')[0].innerHTML = '<span></span>{unread_caption}<em></em>'.substitute({
                        unread_caption: unread_count_caption ? ' <u>' + unread_count_caption + '</u>' : ''
                    });
                }
            });
        };
        var moderateDeletePost = function (button) {
            new futuDialogPopup({
                text: 'Пост взаправду удалится навсегда, точно?',
                type: 'confirm',
                callback: function () {
                    var post = $('p' + params.post_id),
                        url,
                        data;

                    if (post) {
                        $(post).addClass('js-post_deleted');
                    }
                    if (params.wysiwyg) {
                        url = ajaxUrls.post_wysiwyg_delete;
                        data = 'post=' + params.post_id;
                    } else {
                        url = ajaxUrls.post_delete;
                        data = 'id=' + params.post_id;
                    }
                    new futuAjax({
                        button: $(button),
                        attribute: 'opacity',
                        color_to: 0.5,
                        color_from: 1,
                        url: url,
                        data: data,
                        onLoadFunction: function (response) {
                            new futuAlert('Пост удалён.');
                            if (post) {
                                if ($(post).hasClass('js-post_user_banned')) {
                                    futu_controls.hide();
                                    postControlsHandler.removePost(post);
                                    postControlsHandler.redirectPage();
                                } else {
                                    futu_controls.onClose = function () {
                                        postControlsHandler.removePost(post);
                                        postControlsHandler.redirectPage();
                                    };
                                }
                            }
                        }
                    });
                }
            });
        };
        var showBanForm = function (listItem) {
            var domain = '',
                deleteTitle;

            if (globals.base_domain) {
                domain = ' на ' + globals.domain.url.substr(7);
            }
            deleteTitle = 'Удалить все посты и комментарии ' + params.user_login + domain;
            var banForm = '<form action="#" class="b-futu_controls_form b-futu_controls_form_ban"> <span class="b-futu_controls_label_wrap"><label class="b-futu_controls_form_label">забанить ' + params.user_login + ' на</label> ' +
                '<span class="b-form_input_container"><input class="i-form_text_input i-form_text_input__short" name="duration" type="text" onkeyup="blogsSettingsHandler.changeDurationLabel(this);" />' +
                '&nbsp;<label class="b-input_label" id="js-duration_label">(дней)</label></span></span>' +
                '<textarea class="i-form_textarea i-form_textarea__short" name="reason" placeholder="причина"></textarea>' +
                '<input type="checkbox" class="i-chbx" name="delete_user_docs" id="b-delete_user_docs" title="' + deleteTitle + '"/><label for="b-delete_user_docs" title="' + deleteTitle + '" class="b-chbx_label">удалить всё</label>' +
                '<a href="#" class="b-fui_icon_button b-fui_icon_button__submit"><i>OK</i><em></em></a>' +
                '</form>';
            renderControlsForm(listItem, banForm, moderateBanUser);
        };
        var moderateBanUser = function (button) {
            var form = $$('.b-futu_controls_form_ban')[0],
                button = form.getElement('.b-fui_icon_button__submit'),
                durationEl = form.getElement('input[name="duration"]'),
                duration = durationEl ? parseInt(durationEl.value.trim(), 10) : 0,
                reasonEl = form.getElement('textarea[name="reason"]'),
                deleteAllEl = form.getElement('input[name="delete_user_docs"]'),
                genderPronoun = params.user_gender && params.user_gender == 'female' ? 'её' : 'его',
                text = 'Вы уверены, что хотите забанить ' + params.user_login,
                domain = params.domain ? '&domain=' + params.domain : '',
                data = 'users=' + params.user_login + domain;

            if (deleteAllEl.checked) {
                text += ' и удалить все ' + genderPronoun + ' посты и комментарии?'
                data += '&delete_user_docs=true';
            } else {
                text += '?';
            }
            if (duration > 0) {
                data += '&duration=' + duration;
            }
            if (reasonEl) {
                data += '&reason=' + reasonEl.value.trim();
            }
            new futuDialogPopup({
                text: text,
                type: 'confirm',
                callback: function () {
                    var post = $('p' + params.post_id),
                        domain = params.domain ? '&domain=' + params.domain : '';
                    if (post) {
                        $(post).addClass('js-post_user_banned');
                    }
                    new futuAjax({
                        button: $(button),
                        attribute: 'opacity',
                        color_to: 0.5,
                        color_from: 1,
                        url: ajaxUrls.user_ban,
                        data: data,
                        onLoadFunction: function (response) {
                            var message,
                                posts,
                                mainStr = '',
                                closeControl = '';

                            if (response.user_docs_deleted) {
                                message = 'Автор забанен, его посты и комментарии будут удалены через некоторое время.';
                                // Если открыта страница поста, показываем сообщение, что посты и комментарии будут удалены
                                // и предлагаем перейти на главную подсайта
                                if (window.location.href.indexOf('comments') !== -1) {
                                    if (globals.base_domain) {
                                        mainStr = ' подсайта';
                                    }
                                    message += ' А пока можете перейти на <a href="/">главную</a>' + mainStr + '.';
                                    closeControl = true;
                                } else {
                                    posts = $$('.post[data-user_id=' + params.user_id + ']');
                                    posts.each(function (post, i) {
                                        if (post.get('data-domain_id') == params.domain) {
                                            postControlsHandler.removePost(post);
                                        }
                                    });
                                }
                                futu_controls.hide();
                            } else {
                                message = 'Автор забанен.';
                                if (post) {
                                    if ($(post).hasClass('js-post_deleted')) {
                                        futu_controls.hide();
                                        postControlsHandler.removePost(post);
                                    }
                                }
                            }
                            new futuAlert(message, closeControl);
                        }
                    });
                }
            });
        };
        var userIgnore = function (button) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.users_ignore,
                data: 'users=' + params.user_login,
                onLoadFunction: function (response) {
                    var userLinks = $('js-posts_holder').getElements('.c_user[data-user_id="' + params.user_id + '"]'),
                        post;

                    new futuAlert('Вы больше не увидите пользователя ' + params.user_login + '.');
                    futu_controls.hide();

                    for (var i = 0, l = userLinks.length; i < l; i++) {
                        post = userLinks[i].getParent('.post');
                        if (post) {
                            postControlsHandler.removePost(post);
                        }
                    }
                }
            });
        };
        var domainIgnore = function (button) {
            var post = $('p' + params.post_id);

            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.feeds_domains_ignore,
                data: 'domain=' + params.domain_id,
                onLoadFunction: function (response) {
                    var domainLinks = $('js-posts_holder').getElements('.b-post_domain[data-domain_id="' + params.domain_id + '"]'),
                        post;

                    new futuAlert('Вы больше не увидите сайт ' + params.domain_url + '.');
                    futu_controls.hide();

                    for (var i = 0, l = domainLinks.length; i < l; i++) {
                        post = domainLinks[i].getParent('.post');
                        if (post) {
                            postControlsHandler.removePost(post);
                        }
                    }
                }
            });
        };
        var domainUnsubscribe = function (button) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.feeds_domains_unsubscribe,
                data: 'domain=' + params.domain_id,
                onLoadFunction: function (response) {
                    new futuAlert('Вы отписались от домена ' + params.domain_url + '.');
                    futu_controls.hide();
                }
            });
        };
        var userUnsubscribe = function (button) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.feeds_users_unsubscribe,
                data: 'user=' + params.user_id,
                onLoadFunction: function (response) {
                    new futuAlert('Вы отписались от ' + params.user_login + '.');
                    futu_controls.hide();
                }
            });
        };
        var postEdit = function (button) {
            postControlsHandler.showPostEditForm(button, params.post_id, futu_controls, params.post_has_acl);
        };
        var postRenderTypeEdit = function (button) {
            postControlsHandler.showPostEditRenderTypeForm(button, params.post_id, futu_controls);
        };
        var renderControlsForm = function (listItem, formHTML, submitFunction) {
            var initialCoords = listItem.getCoordinates(),
                finalCoords,
                form,
                formStyle,
                marginRight,
                container;

            container = new Element('div', {
                'class': 'b-futu_controls_form_container',
                html: formHTML
            });
            listItem.empty();
            container.inject(listItem);
            form = container.getElement('.b-futu_controls_form');
            finalCoords = form.getCoordinates();
            futu_controls.holder_element.setStyle('max-height', 'none');
            container.setStyles({
                width: initialCoords.width,
                height: initialCoords.height
            });

            formStyle = form.currentStyle || window.getComputedStyle(form);
            marginRight = parseInt(formStyle.marginRight, 10);
            new Fx.Morph(container, {
                duration: 222,
                onComplete: function () {
                    container.addClass('opened');
                    container.erase('style');
                }
            }).start({
                    height: finalCoords.height,
                    width: initialCoords.width < finalCoords.width + marginRight ? finalCoords.width + marginRight : 'auto'
                });
            form.addEvent('submit', function (event) {
                var e = new Event(event);
                e.preventDefault();
                if (typeof submitFunction == 'function') {
                    submitFunction();
                }
            });
            form.getElement('.b-fui_icon_button__submit').addEvent('click', function (event) {
                var e = new Event(event);
                e.preventDefault();
                if (typeof submitFunction == 'function') {
                    submitFunction();
                }
            });
        };
        var showMovingToDraftsForm = function (listItem) {
            var movingToDraftsForm = '<form action="#" class="b-futu_controls_form b-futu_controls_form_drafts"> <span class="b-futu_controls_label_wrap"><label class="b-futu_controls_form_label b-futu_controls_form_label_text">распубликовать</label> ' +
                '<textarea class="i-form_textarea i-form_textarea__short" name="reason" placeholder="причина"></textarea>' +
                '<a href="#" class="b-fui_icon_button b-fui_icon_button__submit"><i>OK</i><em></em></a>' +
                '</form>';
            renderControlsForm(listItem, movingToDraftsForm, movePostToDrafts);
        };
        var movePostToDrafts = function () {
            var form = $$('.b-futu_controls_form_drafts')[0],
                button = form.getElement('.b-fui_icon_button__submit'),
                post = $('p' + params.post_id),
                reasonEl = form.getElement('textarea[name="reason"]'),
                data = 'post=' + params.post_id;

            data += params.domain ? '&domain=' + params.domain : '';
            if (reasonEl && reasonEl.value.trim() != '') {
                data += '&reason=' + reasonEl.value.trim();
            }

            new futuAjax({
                button: button,
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.post_unpublish,
                data: data,
                onLoadFunction: function (response) {
                    if (globals.user.id == params.user_id) {
                        new futuAlert('Вы распубликовали свой пост, <a href="' + globals.parent_site + '/edit/' + params.post_id + '">продолжить его редактирование</a>?', true);
                    } else {
                        new futuAlert('Пост распубликован и отправлен автору на редактирование.');
                    }

                    futu_controls.hide();
                    postControlsHandler.removePost(post);
                }
            });
        };
        var postEditRedirect = function () {
            window.location.href = (globals.base_domain ? globals.base_domain.url : '') + '/edit/' + params.post_id;
        };

        var possible_options = {
            'inbox_delete': {
                caption: 'удалить из инбокса',
                onclick: inboxDelete
            },
            'my_things_delete': {
                caption: 'удалить из моих вещей',
                onclick: myThingsDelete
            },
            'moderate_delete_post': {
                caption: 'удалить пост',
                onclick: moderateDeletePost
            },
            'moderate_ban_user': {
                caption: 'забанить ' + params.user_login,
                onclick: showBanForm
            },
            'favourites_delete': {
                caption: 'удалить из избранного',
                onclick: favouritesDelete
            },
            'domain_unsubscribe': {
                caption: 'отписаться от ' + params.domain_url,
                onclick: domainUnsubscribe
            },
            'domain_ignore': {
                caption: 'игнорировать ' + params.domain_url,
                onclick: domainIgnore
            },
            'user_ignore': {
                caption: 'игнорировать ' + params.user_login,
                onclick: userIgnore
            },
            'post_edit': {
                caption: 'редактировать',
                onclick: postEdit
            },
            'author_edit_link': {
                caption: 'редактировать',
                onclick: postEditRedirect
            },
            'move_to_drafts': {
                caption: 'распубликовать',
                onclick: showMovingToDraftsForm
            },
            'edit_render_type': {
                caption: 'изменить вид поста',
                onclick: postRenderTypeEdit
            },
            'user_unsubscribe': {
                caption: 'отписаться от ' + params.user_login,
                onclick: userUnsubscribe
            },
            'post_edit_redirect': {
                caption: 'редактировать',
                onclick: postEditRedirect
            }
        };
        var options = [];
        var default_options = [
            {
                caption: 'не показывать пост',
                onclick: function (button) {
                    console.log(this, button, post_id);
                }
            },
            {
                caption: 'не показывать автора',
                onclick: function (button) {
                    console.log(this, button, post_id);
                }
            },
            {
                caption: 'отписаться от сайта',
                onclick: function (button) {
                    console.log(this, button, post_id);
                }
            }
        ];
        if (params.options.length > 0) {
            for (var i = 0; i < params.options.length; i++) {
                var option = possible_options[params.options[i]];
                if (option) {
                    options.push(option);
                }
                if (params.options[i] == 'post_edit' || params.options[i] == 'moderate_ban_user' || params.options[i] == 'moderate_ban_and_delete' || params.options[i] == 'moderate_delete_post' || params.options[i] == 'move_to_drafts') {
                    if (params.class_name.indexOf('b-futu_controls_moderate') < 0) {
                        params.class_name += ' b-futu_controls_moderate';
                    }
                    params.close_button_class = 'b-post_controls_moderate_active';
                }
            }
        } else {
            options = default_options;
        }

        var futu_controls = new futuControls({
            button: params.button,
            close_button_class: params.close_button_class,
            onClose: params.onClose,
            options: options,
            class_name: params.class_name
        });
    },
    deletePost: function (button, post_id, domain_url) {
        var postName = 'Пост',
            confirmMessage = postName + ' удалён.';
        if (domain_url && domain_url.indexOf('/my/inbox') !== -1) {
            postName = 'Письмо';
            confirmMessage = postName + ' удалено.';
        }
        new futuDialogPopup({
            text: postName + ' взаправду удалится навсегда, точно?',
            type: 'confirm',
            callback: function () {
                var post_element = $('p' + post_id);
                new futuAjax({
                    button: $(button),
                    attribute: 'opacity',
                    color_to: 0.5,
                    color_from: 1,
                    url: ajaxUrls.post_delete,
                    data: 'id=' + post_id,
                    onLoadFunction: function (response) {
                        new futuAlert(confirmMessage);

                        postControlsHandler.removePost(post_element);
                        postControlsHandler.redirectPage();
                    }
                });
            }
        });
    },
    removePost: function (post) {
        if (window.location.href.indexOf('comments') !== -1) {
            var container = $$('.l-i-wrapper');
            container.set('morph', {duration: 333});
            container.morph({opacity: 0.2})
        } else {
            var post_header = $$('.b-post_header');
            post.set('morph', {duration: 333, onComplete: function () {
                post.destroy();
            }});
            post.style.overflow = 'hidden';
            post.morph({'height': 0, 'paddingBottom': 0, 'paddingTop': 0});
        }

    },
    redirectPage: function () {
        // Если находимся на странице самого поста делаем редирект
        // на главную страницу или на главную подсайта
        if (window.location.href.indexOf('comments') !== -1) {
            (function () {
                window.location.href = '/';
            }).delay(500);
        }
    },
    showPostEditForm: function (button, post_id, futu_controls, post_has_acl) {
        var post = $('p' + post_id);
        if (!post.getElement('.b-post_edit_holder')) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.getPostUrl(post_id, post_has_acl),
                data: '',
                onLoadFunction: function (response) {
                    futu_controls.hide();


                    if (post.getParent('.post_comments_page')) {
                        if ($$('.post_inbox_page').length == 0) {
                            post.getElement('h3').addClass('hidden');
                        }
                        if ($('js-tags_public')) {
                            $('js-tags_public').addClass('hidden');
                        }
                    }

                    var post_body_height = post.getElement('.dt').offsetHeight;

                    var mod_window = new Element('div', {
                        'class': 'b-post_edit_holder',
                        'style': 'height:' + post_body_height + 'px;'
                    });
                    mod_window.innerHTML = response.template;

                    // replace all br tags with line breaks
                    mod_window.getElement('textarea[name="body"]').value = mod_window.getElement('textarea[name="body"]').value.replace(/<br>/gi, '\n');

                    mod_window.inject(post.getElement('.dt'), 'after');
                    post.getElement('.dt').addClass('hidden');

                    new wysiwyg(mod_window.getElement('.b-textarea_editor'), mod_window.getElement('textarea[name="body"]'), response.user_is_moderator);

                    var mod_window_height = mod_window.getElement('.b-post_edit').offsetHeight;
                    mod_window.set('morph', {
                        duration: 222,
                        onComplete: function () {
                            mod_window.style.height = 'auto';
                        }
                    });

                    mod_window.morph({height: mod_window_height});

                    if (mod_window.getElement('.js-new_post_tags')) {
                        tagsHandler.init({
                            input: mod_window.getElement('.js-new_post_tags'),
                            hiddenListInput: mod_window.getElement('.js-tags_list'),
                            container: mod_window.getElement('.js-tags_list_container'),
                            collectionContainer: mod_window.getElement('.js-popular_tags'),
                            type: 'post'
                        });
                    }

                    postControlsHandler.initFileUploader(post_id);

                    mod_window.getElement('textarea[name="body"]').addEvent('keydown', (function (e) {
                        if ((e.meta || e.control) && e.code == 13) {
                            var e = new Event(e);
                            e.preventDefault();
                            postControlsHandler.savePost(post_id, post_has_acl);
                        }
                    }).bind(this));
                }
            });
        } else {
            futu_controls.hide();
        }
    },
    showPostEditRenderTypeForm: function (button, post_id, futu_controls) {
        var post = $('p' + post_id);
        if (!post.getElement('.b-post_edit_holder')) {
            new futuAjax({
                button: $(button),
                attribute: 'opacity',
                color_to: 0.5,
                color_from: 1,
                url: ajaxUrls.getPostUrl(post_id),
                data: '',
                onLoadFunction: function (response) {
                    var post_body_height,
                        sibling_element,
                        class_name = '';
                    futu_controls.hide();

                    if (postControlsHandler.isCommentsPage()) {
                        post_body_height = 0;
                        sibling_element = $$('.b-post_header')[0];
                        class_name = 'b-post_wrapper';
                    } else {
                        post_body_height = post.getElement('.dt').offsetHeight;
                        sibling_element = post.getElement('.dt');
                        sibling_element.addClass('hidden');
                    }

                    var mod_window = new Element('div', {
                        'class': 'b-post_edit_holder ' + class_name,
                        'style': 'height:' + post_body_height + 'px;'
                    });
                    mod_window.innerHTML = response.template;

                    mod_window.inject(sibling_element, 'after');

                    var mod_window_height = mod_window.getElement('.b-post_edit').offsetHeight;

                    mod_window.set('morph', {
                        duration: 222,
                        onComplete: function () {
                            mod_window.style.height = 'auto';
                        }
                    });

                    mod_window.morph({height: mod_window_height});

                    if (window.getScroll().y > mod_window.getPosition().y) {
                        animatePosts.scrollTo(mod_window);
                    }
                }
            });
        } else {
            futu_controls.hide();
        }
    },
    initFileUploader: function (post_id) {
        if (!utils.isFileUploadSupported()) {
            $('js-file_uploader_' + post_id).addClass('hidden');
            return;
        }

        if ($('js-file_uploader_button_' + post_id)) {
            new futuFileUploader({
                container: 'js-file_uploader_' + post_id,
                browseButton: 'js-file_uploader_button_' + post_id,
                uploadProgress: function (up, file) {
                    var progress_element = $('js-file_uploader_progress_' + post_id);
                    progress_element.removeClass('hidden');
                    progress_element.innerHTML = file.name + '<br>(' + file.percent + '%)';
                }.bind(this),
                uploadComplete: function (up, file, response) {
                    var progress_element = $('js-file_uploader_progress_' + post_id);
                    ajaxHandler.highlightField(progress_element, '#FFFFFF', '#556E8C');
                }.bind(this),
                fileUploaded: function (up, file, response) {
                    var mod_form = $('js-mod_form_' + post_id);
                    var response = JSON.decode(response.response);
                    if (response.media_id) {
                        if (mod_form.getElement('.b-post_edit_pic_id')) {
                            mod_form.getElement('.b-post_edit_pic_id').value = response.media_id;
                        } else {
                            var media_input = new Element('input', {
                                type: 'hidden',
                                name: 'media',
                                value: response.media_id,
                                'class': 'b-post_edit_pic_id'
                            });
                            media_input.inject(mod_form);
                            mod_form.getElement('.b-post_edit_delete_pic').removeClass('hidden');
                            mod_form.getElement('.b-file_uploader a').innerHTML = 'сменить';
                        }
                    } else {
                        if (response.status == 'ERR') {
                            for (var i = 0; i < response.errors.length; i++) {
                                ajaxHandler.alertError(localMessages.getErrorMessageByError(response.errors[i]));
                            }
                            return false;
                        }
                    }
                },
                uploadErrors: {
                    'status_413': function () {
                        var progress_element = $('js-file_uploader_progress_' + post_id);
                        progress_element.addClass('hidden');
                    }
                }
            });
        }
    },
    removePic: function (post_id) {
        var mod_form = $('js-mod_form_' + post_id);
        var progress_element = $('js-file_uploader_progress_' + post_id);

        mod_form.getElement('.b-post_edit_delete_pic').addClass('hidden');
        mod_form.getElement('.b-file_uploader a').innerHTML = 'Закачать картинку';

        progress_element.addClass('hidden');
        progress_element.innerHTML = '';

        if (mod_form.getElement('.b-post_edit_pic img')) {
            mod_form.getElement('.b-post_edit_pic img').destroy();
        }
        if (mod_form.getElement('.b-post_edit_pic_id')) {
            mod_form.getElement('.b-post_edit_pic_id').value = '';
        }
    },
    removePostEditForm: function (post_id) {
        var post = $('p' + post_id);
        var form = $('js-mod_form_' + post_id);
        var post_dt_el = post.getElement('.dt');

        if (post_dt_el) {
            post_dt_el.removeClass('hidden');
        }
        form.getParent('.b-post_edit_holder').destroy();

        if (post.getParent('.post_comments_page')) {
            if ($('js-tags_public') && !$('js-tags_public').hasClass('hidden')) {
                $('js-post_tags').removeClass('hidden');
            }
            post.getElement('h3').removeClass('hidden');
        }
    },
    validatePost: function (post_id) {
        var form = $('js-mod_form_' + post_id);
        if (form.getElement('.i-mod_form_caption') && form.getElement('.i-mod_form_caption').value.trim().length < 1) {
            new futuAlert('Заголовок у поста теперь обязателен.');
            form.getElement('.i-mod_form_caption').focus();
            return false;
        }
        if (form.getElement('.i-mod_form_body') && form.getElement('.i-mod_form_url')) {
            if (form.getElement('.i-mod_form_body').value.trim().length < 1 && form.getElement('.i-mod_form_url').value.trim().length < 1 && (!form.getElement('.b-post_edit_pic_id') || form.getElement('.b-post_edit_pic_id').value == '')) {
                form.getElement('.i-mod_form_url').focus();
                new futuAlert('Пост должен содержать ссылку или текст&nbsp;&mdash; одно из двух, или всё сразу. Но никак не ни то, ни другое одновременно.');
                return false;
            }
        } else {
            if (form.getElement('.i-mod_form_body').value.trim().length < 1) {
                new futuAlert('Укажите, пожалуйста, текст.');
                return false;
            }
        }

        return true;
    },
    savePost: function (post_id, post_has_acl) {
        var form = $('js-mod_form_' + post_id);
        var post = $('p' + post_id);

        if (postControlsHandler.validatePost(post_id)) {
            var data = post_has_acl ? $('js-mod_form_' + post_id).toQueryString() : '',
                url;

            if (!post_has_acl) {
                var data_fields = {
                    type: 'post',
                    title: form.getElement('.i-mod_form_caption').value,
                    content: form.getElement('.i-mod_form_body').value,
                    url: form.getElement('.i-mod_form_url').value,
                    image: null
                };
                var image_input = form.getElement('.b-post_edit_pic_id');
                if (image_input && parseInt(image_input.value, 10) > 0) {
                    data_fields.image = image_input.value.trim();
                }
                data += '&post=' + post_id;
                if (form.getElement('.js-tags_list')) {
                    data += '&tags=' + encodeURIComponent(form.getElement('.js-tags_list').value);
                }
                data += '&wysiwyg_data=' + encodeURIComponent(JSON.encode(data_fields));
            }

            new futuAjax({
                button: $('js-mod_form_' + post_id).getElement('.b-post_edit_submit'),
                color_to: Colors.links_color,
                color_from: Colors.background_color,
                url: post_has_acl ? ajaxUrls.inbox_edit : ajaxUrls.post_link_edit,
                data: data,
                onLoadFunction: function (response) {

                    if (!form.getElement('.b-post_render_types') || (form.getElement('.b-post_render_types') && form.getElements('input[name="render_type"]:checked').length == 0)) {
                        postControlsHandler.setChangedPostByResponse(post_id, response);
                    } else {
                        if (postControlsHandler.isCommentsPage()) {
                            postControlsHandler.setTagsOnCommentsPage(response.post.tags);
                        }
                    }

                    if (form.getElement('.b-post_render_types') && form.getElements('input[name="render_type"]:checked').length > 0) {
                        var form_objects = $('js-mod_form_' + post_id).toQueryString().parseQueryString();
                        var data = 'post=' + post_id;
                        data += '&render_type=' + form_objects.render_type;
                        data += '&domain=' + form_objects.domain;
                        new futuAjax({
                            button: form.getElement('.b-post_edit_submit'),
                            color_to: Colors.links_color,
                            color_from: Colors.background_color,
                            url: ajaxUrls.post_render_type,
                            data: data,
                            onLoadFunction: function (response) {
                                postControlsHandler.setChangedPostByResponse(post_id, response);
                            }
                        });
                    }

                }
            });
        }
    },

    setChangedPostByResponse: function (post_id, response) {
        var post = $('p' + post_id);
        postControlsHandler.removePostEditForm(post_id);

        if (!postControlsHandler.isCommentsPage()) {
            post.getElement('.dt').innerHTML = response.template;
        } else {
            post.getElement('.dt').innerHTML = response.template;
            postControlsHandler.setTagsOnCommentsPage(response.post.tags);
            postControlsHandler.setCaptionOnCommentsPage(post_id, response.post);
        }
        futuPics.initExpandingPics();
        animatePosts.scrollTo(post);
        audioHandler.init();
    },

    setCaptionOnCommentsPage: function (post_id, post_object) {
        var post = $('p' + post_id);
        var post_attributes = post_object.attributes.wysiwyg_data ? JSON.decode(post_object.attributes.wysiwyg_data) : post_object.attributes;

        if (post_attributes.url) {
            post.getElement('h3').innerHTML = '<a href="' + post_attributes.url + '"></a>';
            post.getElement('h3 a').innerHTML = post_attributes.caption ? post_attributes.caption : post_attributes.title;
        } else {
            post.getElement('h3').innerHTML = post_attributes.caption ? post_attributes.caption : post_attributes.title;
        }
        if (post.getElement('.b-fui_icon_button_close')) {
            post.getElement('.b-fui_icon_button_close').destroy();
        }
        post.getElement('h3').removeClass('hidden');
        if (post.getElement('.dt h3')) {
            post.getElement('.dt h3').destroy();
        }
    },
    setTagsOnCommentsPage: function (tags) {
        var counter = 0;
        if (tags) {
            for (var i in tags) {
                if (tags.hasOwnProperty(i)) {
                    var tag = tags[i];
                    var postTags = $('js-post_tags');
                    var postTagsPublic = $('js-tags_public');
                    if (counter == 0) {
                        if (postTags) {
                            postTags.removeClass('hidden');
                        }
                        if (postTagsPublic) {
                            $('js-tags_public').removeClass('hidden');
                            if (!$('js-tags_public').getElement('ul')) {
                                (new Element('ul')).inject($('js-tags_public'));
                            } else {
                                $('js-tags_public').getElement('ul').innerHTML = '';
                            }
                        }
                    }
                    counter++;

                    var tag_iHTML = '<a href="/tag/' + tag.body + '" class="tag">' + tag.body + '</a>&nbsp;';
                    (new Element('li', {
                        'id': 'js-public_tag_' + tag.id,
                        'html': tag_iHTML
                    })).inject($('js-tags_public').getElement('ul'));
                }
            }
        }
    },

    savePostRenderType: function (post_id) {
        var data = $('js-mod_form_' + post_id).toQueryString();

        new futuAjax({
            button: $('js-mod_form_' + post_id).getElement('.b-post_edit_submit'),
            color_to: Colors.links_color,
            color_from: Colors.background_color,
            url: ajaxUrls.post_render_type,
            data: data,
            onLoadFunction: function (response) {
                postControlsHandler.removePostEditForm(post_id);

                if (!postControlsHandler.isCommentsPage()) {
                    var post = $('p' + post_id);

                    if (response.template) {
                        post.getElement('.dt').innerHTML = response.template;
                        futuPics.initExpandingPics();
                    }
                    animatePosts.scrollTo(post);
                    audioHandler.init();
                } else {
                    new futuAlert('Вид поста обновлен.');
                }
            }
        });
    },
    isCommentsPage: function () {
        if (window.location.href.indexOf('comments') !== -1) {
            return true;
        } else {
            return false;
        }
    }
};
/*1.5.4*/
(function () {
    var f = 0, k = [], m = {}, i = {}, a = {"<": "lt", ">": "gt", "&": "amp", '"': "quot", "'": "#39"}, l = /[<>&\"\']/g, b, c = window.setTimeout, d = {}, e;

    function h() {
        this.returnValue = false
    }

    function j() {
        this.cancelBubble = true
    }

    (function (n) {
        var o = n.split(/,/), p, r, q;
        for (p = 0; p < o.length; p += 2) {
            q = o[p + 1].split(/ /);
            for (r = 0; r < q.length; r++) {
                i[q[r]] = o[p]
            }
        }
    })("application/msword,doc dot,application/pdf,pdf,application/pgp-signature,pgp,application/postscript,ps ai eps,application/rtf,rtf,application/vnd.ms-excel,xls xlb,application/vnd.ms-powerpoint,ppt pps pot,application/zip,zip,application/x-shockwave-flash,swf swfl,application/vnd.openxmlformats-officedocument.wordprocessingml.document,docx,application/vnd.openxmlformats-officedocument.wordprocessingml.template,dotx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,xlsx,application/vnd.openxmlformats-officedocument.presentationml.presentation,pptx,application/vnd.openxmlformats-officedocument.presentationml.template,potx,application/vnd.openxmlformats-officedocument.presentationml.slideshow,ppsx,application/x-javascript,js,application/json,json,audio/mpeg,mpga mpega mp2 mp3,audio/x-wav,wav,audio/mp4,m4a,image/bmp,bmp,image/gif,gif,image/jpeg,jpeg jpg jpe,image/photoshop,psd,image/png,png,image/svg+xml,svg svgz,image/tiff,tiff tif,text/plain,asc txt text diff log,text/html,htm html xhtml,text/css,css,text/csv,csv,text/rtf,rtf,video/mpeg,mpeg mpg mpe,video/quicktime,qt mov,video/mp4,mp4,video/x-m4v,m4v,video/x-flv,flv,video/x-ms-wmv,wmv,video/avi,avi,video/webm,webm,video/vnd.rn-realvideo,rv,application/vnd.oasis.opendocument.formula-template,otf,application/octet-stream,exe");
    var g = {VERSION: "1.5.4", STOPPED: 1, STARTED: 2, QUEUED: 1, UPLOADING: 2, FAILED: 4, DONE: 5, GENERIC_ERROR: -100, HTTP_ERROR: -200, IO_ERROR: -300, SECURITY_ERROR: -400, INIT_ERROR: -500, FILE_SIZE_ERROR: -600, FILE_EXTENSION_ERROR: -601, IMAGE_FORMAT_ERROR: -700, IMAGE_MEMORY_ERROR: -701, IMAGE_DIMENSIONS_ERROR: -702, mimeTypes: i, ua: (function () {
        var r = navigator, q = r.userAgent, s = r.vendor, o, n, p;
        o = /WebKit/.test(q);
        p = o && s.indexOf("Apple") !== -1;
        n = window.opera && window.opera.buildNumber;
        return{windows: navigator.platform.indexOf("Win") !== -1, ie: !o && !n && (/MSIE/gi).test(q) && (/Explorer/gi).test(r.appName), webkit: o, gecko: !o && /Gecko/.test(q), safari: p, opera: !!n}
    }()), typeOf: function (n) {
        return({}).toString.call(n).match(/\s([a-z|A-Z]+)/)[1].toLowerCase()
    }, extend: function (n) {
        g.each(arguments, function (o, p) {
            if (p > 0) {
                g.each(o, function (r, q) {
                    n[q] = r
                })
            }
        });
        return n
    }, cleanName: function (n) {
        var o, p;
        p = [/[\300-\306]/g, "A", /[\340-\346]/g, "a", /\307/g, "C", /\347/g, "c", /[\310-\313]/g, "E", /[\350-\353]/g, "e", /[\314-\317]/g, "I", /[\354-\357]/g, "i", /\321/g, "N", /\361/g, "n", /[\322-\330]/g, "O", /[\362-\370]/g, "o", /[\331-\334]/g, "U", /[\371-\374]/g, "u"];
        for (o = 0; o < p.length; o += 2) {
            n = n.replace(p[o], p[o + 1])
        }
        n = n.replace(/\s+/g, "_");
        n = n.replace(/[^a-z0-9_\-\.]+/gi, "");
        return n
    }, addRuntime: function (n, o) {
        o.name = n;
        k[n] = o;
        k.push(o);
        return o
    }, guid: function () {
        var n = new Date().getTime().toString(32), o;
        for (o = 0; o < 5; o++) {
            n += Math.floor(Math.random() * 65535).toString(32)
        }
        return(g.guidPrefix || "p") + n + (f++).toString(32)
    }, buildUrl: function (o, n) {
        var p = "";
        g.each(n, function (r, q) {
            p += (p ? "&" : "") + encodeURIComponent(q) + "=" + encodeURIComponent(r)
        });
        if (p) {
            o += (o.indexOf("?") > 0 ? "&" : "?") + p
        }
        return o
    }, each: function (q, r) {
        var p, o, n;
        if (q) {
            p = q.length;
            if (p === b) {
                for (o in q) {
                    if (q.hasOwnProperty(o)) {
                        if (r(q[o], o) === false) {
                            return
                        }
                    }
                }
            } else {
                for (n = 0; n < p; n++) {
                    if (r(q[n], n) === false) {
                        return
                    }
                }
            }
        }
    }, formatSize: function (n) {
        if (n === b || /\D/.test(n)) {
            return g.translate("N/A")
        }
        if (n > 1073741824) {
            return Math.round(n / 1073741824, 1) + " GB"
        }
        if (n > 1048576) {
            return Math.round(n / 1048576, 1) + " MB"
        }
        if (n > 1024) {
            return Math.round(n / 1024, 1) + " KB"
        }
        return n + " b"
    }, getPos: function (o, s) {
        var t = 0, r = 0, v, u = document, p, q;
        o = o;
        s = s || u.body;
        function n(B) {
            var z, A, w = 0, C = 0;
            if (B) {
                A = B.getBoundingClientRect();
                z = u.compatMode === "CSS1Compat" ? u.documentElement : u.body;
                w = A.left + z.scrollLeft;
                C = A.top + z.scrollTop
            }
            return{x: w, y: C}
        }

        if (o && o.getBoundingClientRect && ((navigator.userAgent.indexOf("MSIE") > 0) && (u.documentMode < 8))) {
            p = n(o);
            q = n(s);
            return{x: p.x - q.x, y: p.y - q.y}
        }
        v = o;
        while (v && v != s && v.nodeType) {
            t += v.offsetLeft || 0;
            r += v.offsetTop || 0;
            v = v.offsetParent
        }
        v = o.parentNode;
        while (v && v != s && v.nodeType) {
            t -= v.scrollLeft || 0;
            r -= v.scrollTop || 0;
            v = v.parentNode
        }
        return{x: t, y: r}
    }, getSize: function (n) {
        return{w: n.offsetWidth || n.clientWidth, h: n.offsetHeight || n.clientHeight}
    }, parseSize: function (n) {
        var o;
        if (typeof(n) == "string") {
            n = /^([0-9]+)([mgk]?)$/.exec(n.toLowerCase().replace(/[^0-9mkg]/g, ""));
            o = n[2];
            n = +n[1];
            if (o == "g") {
                n *= 1073741824
            }
            if (o == "m") {
                n *= 1048576
            }
            if (o == "k") {
                n *= 1024
            }
        }
        return n
    }, xmlEncode: function (n) {
        return n ? ("" + n).replace(l, function (o) {
            return a[o] ? "&" + a[o] + ";" : o
        }) : n
    }, toArray: function (p) {
        var o, n = [];
        for (o = 0; o < p.length; o++) {
            n[o] = p[o]
        }
        return n
    }, inArray: function (p, q) {
        if (q) {
            if (Array.prototype.indexOf) {
                return Array.prototype.indexOf.call(q, p)
            }
            for (var n = 0, o = q.length; n < o; n++) {
                if (q[n] === p) {
                    return n
                }
            }
        }
        return -1
    }, addI18n: function (n) {
        return g.extend(m, n)
    }, translate: function (n) {
        return m[n] || n
    }, isEmptyObj: function (n) {
        if (n === b) {
            return true
        }
        for (var o in n) {
            return false
        }
        return true
    }, hasClass: function (p, o) {
        var n;
        if (p.className == "") {
            return false
        }
        n = new RegExp("(^|\\s+)" + o + "(\\s+|$)");
        return n.test(p.className)
    }, addClass: function (o, n) {
        if (!g.hasClass(o, n)) {
            o.className = o.className == "" ? n : o.className.replace(/\s+$/, "") + " " + n
        }
    }, removeClass: function (p, o) {
        var n = new RegExp("(^|\\s+)" + o + "(\\s+|$)");
        p.className = p.className.replace(n, function (r, q, s) {
            return q === " " && s === " " ? " " : ""
        })
    }, getStyle: function (o, n) {
        if (o.currentStyle) {
            return o.currentStyle[n]
        } else {
            if (window.getComputedStyle) {
                return window.getComputedStyle(o, null)[n]
            }
        }
    }, addEvent: function (s, n, t) {
        var r, q, p, o;
        o = arguments[3];
        n = n.toLowerCase();
        if (e === b) {
            e = "Plupload_" + g.guid()
        }
        if (s.addEventListener) {
            r = t;
            s.addEventListener(n, r, false)
        } else {
            if (s.attachEvent) {
                r = function () {
                    var u = window.event;
                    if (!u.target) {
                        u.target = u.srcElement
                    }
                    u.preventDefault = h;
                    u.stopPropagation = j;
                    t(u)
                };
                s.attachEvent("on" + n, r)
            }
        }
        if (s[e] === b) {
            s[e] = g.guid()
        }
        if (!d.hasOwnProperty(s[e])) {
            d[s[e]] = {}
        }
        q = d[s[e]];
        if (!q.hasOwnProperty(n)) {
            q[n] = []
        }
        q[n].push({func: r, orig: t, key: o})
    }, removeEvent: function (s, n) {
        var q, t, p;
        if (typeof(arguments[2]) == "function") {
            t = arguments[2]
        } else {
            p = arguments[2]
        }
        n = n.toLowerCase();
        if (s[e] && d[s[e]] && d[s[e]][n]) {
            q = d[s[e]][n]
        } else {
            return
        }
        for (var o = q.length - 1; o >= 0; o--) {
            if (q[o].key === p || q[o].orig === t) {
                if (s.removeEventListener) {
                    s.removeEventListener(n, q[o].func, false)
                } else {
                    if (s.detachEvent) {
                        s.detachEvent("on" + n, q[o].func)
                    }
                }
                q[o].orig = null;
                q[o].func = null;
                q.splice(o, 1);
                if (t !== b) {
                    break
                }
            }
        }
        if (!q.length) {
            delete d[s[e]][n]
        }
        if (g.isEmptyObj(d[s[e]])) {
            delete d[s[e]];
            try {
                delete s[e]
            } catch (r) {
                s[e] = b
            }
        }
    }, removeAllEvents: function (o) {
        var n = arguments[1];
        if (o[e] === b || !o[e]) {
            return
        }
        g.each(d[o[e]], function (q, p) {
            g.removeEvent(o, p, n)
        })
    }};
    g.Uploader = function (r) {
        var o = {}, u, t = [], q, p = false;
        u = new g.QueueProgress();
        r = g.extend({chunk_size: 0, multipart: true, multi_selection: true, file_data_name: "file", filters: []}, r);
        function s() {
            var w, x = 0, v;
            if (this.state == g.STARTED) {
                for (v = 0; v < t.length; v++) {
                    if (!w && t[v].status == g.QUEUED) {
                        w = t[v];
                        w.status = g.UPLOADING;
                        if (this.trigger("BeforeUpload", w)) {
                            this.trigger("UploadFile", w)
                        }
                    } else {
                        x++
                    }
                }
                if (x == t.length) {
                    this.stop();
                    this.trigger("UploadComplete", t)
                }
            }
        }

        function n() {
            var w, v;
            u.reset();
            for (w = 0; w < t.length; w++) {
                v = t[w];
                if (v.size !== b) {
                    u.size += v.size;
                    u.loaded += v.loaded
                } else {
                    u.size = b
                }
                if (v.status == g.DONE) {
                    u.uploaded++
                } else {
                    if (v.status == g.FAILED) {
                        u.failed++
                    } else {
                        u.queued++
                    }
                }
            }
            if (u.size === b) {
                u.percent = t.length > 0 ? Math.ceil(u.uploaded / t.length * 100) : 0
            } else {
                u.bytesPerSec = Math.ceil(u.loaded / ((+new Date() - q || 1) / 1000));
                u.percent = u.size > 0 ? Math.ceil(u.loaded / u.size * 100) : 0
            }
        }

        g.extend(this, {state: g.STOPPED, runtime: "", features: {}, files: t, settings: r, total: u, id: g.guid(), init: function () {
            var A = this, B, x, w, z = 0, y;
            if (typeof(r.preinit) == "function") {
                r.preinit(A)
            } else {
                g.each(r.preinit, function (D, C) {
                    A.bind(C, D)
                })
            }
            r.page_url = r.page_url || document.location.pathname.replace(/\/[^\/]+$/g, "/");
            if (!/^(\w+:\/\/|\/)/.test(r.url)) {
                r.url = r.page_url + r.url
            }
            r.chunk_size = g.parseSize(r.chunk_size);
            r.max_file_size = g.parseSize(r.max_file_size);
            A.bind("FilesAdded", function (C, F) {
                var E, D, H = 0, I, G = r.filters;
                if (G && G.length) {
                    I = [];
                    g.each(G, function (J) {
                        g.each(J.extensions.split(/,/), function (K) {
                            if (/^\s*\*\s*$/.test(K)) {
                                I.push("\\.*")
                            } else {
                                I.push("\\." + K.replace(new RegExp("[" + ("/^$.*+?|()[]{}\\".replace(/./g, "\\$&")) + "]", "g"), "\\$&"))
                            }
                        })
                    });
                    I = new RegExp(I.join("|") + "$", "i")
                }
                for (E = 0; E < F.length; E++) {
                    D = F[E];
                    D.loaded = 0;
                    D.percent = 0;
                    D.status = g.QUEUED;
                    if (I && !I.test(D.name)) {
                        C.trigger("Error", {code: g.FILE_EXTENSION_ERROR, message: g.translate("File extension error."), file: D});
                        continue
                    }
                    if (D.size !== b && D.size > r.max_file_size) {
                        C.trigger("Error", {code: g.FILE_SIZE_ERROR, message: g.translate("File size error."), file: D});
                        continue
                    }
                    t.push(D);
                    H++
                }
                if (H) {
                    c(function () {
                        A.trigger("QueueChanged");
                        A.refresh()
                    }, 1)
                } else {
                    return false
                }
            });
            if (r.unique_names) {
                A.bind("UploadFile", function (C, D) {
                    var F = D.name.match(/\.([^.]+)$/), E = "tmp";
                    if (F) {
                        E = F[1]
                    }
                    D.target_name = D.id + "." + E
                })
            }
            A.bind("UploadProgress", function (C, D) {
                D.percent = D.size > 0 ? Math.ceil(D.loaded / D.size * 100) : 100;
                n()
            });
            A.bind("StateChanged", function (C) {
                if (C.state == g.STARTED) {
                    q = (+new Date())
                } else {
                    if (C.state == g.STOPPED) {
                        for (B = C.files.length - 1; B >= 0; B--) {
                            if (C.files[B].status == g.UPLOADING) {
                                C.files[B].status = g.QUEUED;
                                n()
                            }
                        }
                    }
                }
            });
            A.bind("QueueChanged", n);
            A.bind("Error", function (C, D) {
                if (D.file) {
                    D.file.status = g.FAILED;
                    n();
                    if (C.state == g.STARTED) {
                        c(function () {
                            s.call(A)
                        }, 1)
                    }
                }
            });
            A.bind("FileUploaded", function (C, D) {
                D.status = g.DONE;
                D.loaded = D.size;
                C.trigger("UploadProgress", D);
                c(function () {
                    s.call(A)
                }, 1)
            });
            if (r.runtimes) {
                x = [];
                y = r.runtimes.split(/\s?,\s?/);
                for (B = 0; B < y.length; B++) {
                    if (k[y[B]]) {
                        x.push(k[y[B]])
                    }
                }
            } else {
                x = k
            }
            function v() {
                var F = x[z++], E, C, D;
                if (F) {
                    E = F.getFeatures();
                    C = A.settings.required_features;
                    if (C) {
                        C = C.split(",");
                        for (D = 0; D < C.length; D++) {
                            if (!E[C[D]]) {
                                v();
                                return
                            }
                        }
                    }
                    F.init(A, function (G) {
                        if (G && G.success) {
                            A.features = E;
                            A.runtime = F.name;
                            A.trigger("Init", {runtime: F.name});
                            A.trigger("PostInit");
                            A.refresh()
                        } else {
                            v()
                        }
                    })
                } else {
                    A.trigger("Error", {code: g.INIT_ERROR, message: g.translate("Init error.")})
                }
            }

            v();
            if (typeof(r.init) == "function") {
                r.init(A)
            } else {
                g.each(r.init, function (D, C) {
                    A.bind(C, D)
                })
            }
        }, refresh: function () {
            this.trigger("Refresh")
        }, start: function () {
            if (t.length && this.state != g.STARTED) {
                this.state = g.STARTED;
                this.trigger("StateChanged");
                s.call(this)
            }
        }, stop: function () {
            if (this.state != g.STOPPED) {
                this.state = g.STOPPED;
                this.trigger("CancelUpload");
                this.trigger("StateChanged")
            }
        }, disableBrowse: function () {
            p = arguments[0] !== b ? arguments[0] : true;
            this.trigger("DisableBrowse", p)
        }, getFile: function (w) {
            var v;
            for (v = t.length - 1; v >= 0; v--) {
                if (t[v].id === w) {
                    return t[v]
                }
            }
        }, removeFile: function (w) {
            var v;
            for (v = t.length - 1; v >= 0; v--) {
                if (t[v].id === w.id) {
                    return this.splice(v, 1)[0]
                }
            }
        }, splice: function (x, v) {
            var w;
            w = t.splice(x === b ? 0 : x, v === b ? t.length : v);
            this.trigger("FilesRemoved", w);
            this.trigger("QueueChanged");
            return w
        }, trigger: function (w) {
            var y = o[w.toLowerCase()], x, v;
            if (y) {
                v = Array.prototype.slice.call(arguments);
                v[0] = this;
                for (x = 0; x < y.length; x++) {
                    if (y[x].func.apply(y[x].scope, v) === false) {
                        return false
                    }
                }
            }
            return true
        }, hasEventListener: function (v) {
            return !!o[v.toLowerCase()]
        }, bind: function (v, x, w) {
            var y;
            v = v.toLowerCase();
            y = o[v] || [];
            y.push({func: x, scope: w || this});
            o[v] = y
        }, unbind: function (v) {
            v = v.toLowerCase();
            var y = o[v], w, x = arguments[1];
            if (y) {
                if (x !== b) {
                    for (w = y.length - 1; w >= 0; w--) {
                        if (y[w].func === x) {
                            y.splice(w, 1);
                            break
                        }
                    }
                } else {
                    y = []
                }
                if (!y.length) {
                    delete o[v]
                }
            }
        }, unbindAll: function () {
            var v = this;
            g.each(o, function (x, w) {
                v.unbind(w)
            })
        }, destroy: function () {
            this.stop();
            this.trigger("Destroy");
            this.unbindAll()
        }})
    };
    g.File = function (q, o, p) {
        var n = this;
        n.id = q;
        n.name = o;
        n.size = p;
        n.loaded = 0;
        n.percent = 0;
        n.status = 0
    };
    g.Runtime = function () {
        this.getFeatures = function () {
        };
        this.init = function (n, o) {
        }
    };
    g.QueueProgress = function () {
        var n = this;
        n.size = 0;
        n.loaded = 0;
        n.uploaded = 0;
        n.failed = 0;
        n.queued = 0;
        n.percent = 0;
        n.bytesPerSec = 0;
        n.reset = function () {
            n.size = n.loaded = n.uploaded = n.failed = n.queued = n.percent = n.bytesPerSec = 0
        }
    };
    g.runtimes = {};
    window.plupload = g
})();
(function (h, k, j, e) {
    var c = {}, g;

    function m(o, p) {
        var n;
        if ("FileReader" in h) {
            n = new FileReader();
            n.readAsDataURL(o);
            n.onload = function () {
                p(n.result)
            }
        } else {
            return p(o.getAsDataURL())
        }
    }

    function l(o, p) {
        var n;
        if ("FileReader" in h) {
            n = new FileReader();
            n.readAsBinaryString(o);
            n.onload = function () {
                p(n.result)
            }
        } else {
            return p(o.getAsBinary())
        }
    }

    function d(r, p, n, v) {
        var q, o, u, s, t = this;
        m(c[r.id], function (w) {
            q = k.createElement("canvas");
            q.style.display = "none";
            k.body.appendChild(q);
            o = q.getContext("2d");
            u = new Image();
            u.onerror = u.onabort = function () {
                v({success: false})
            };
            u.onload = function () {
                var B, x, z, y, A;
                if (!p.width) {
                    p.width = u.width
                }
                if (!p.height) {
                    p.height = u.height
                }
                s = Math.min(p.width / u.width, p.height / u.height);
                if (s < 1 || (s === 1 && n === "image/jpeg")) {
                    B = Math.round(u.width * s);
                    x = Math.round(u.height * s);
                    q.width = B;
                    q.height = x;
                    o.drawImage(u, 0, 0, B, x);
                    if (n === "image/jpeg") {
                        y = new f(atob(w.substring(w.indexOf("base64,") + 7)));
                        if (y.headers && y.headers.length) {
                            A = new a();
                            if (A.init(y.get("exif")[0])) {
                                A.setExif("PixelXDimension", B);
                                A.setExif("PixelYDimension", x);
                                y.set("exif", A.getBinary());
                                if (t.hasEventListener("ExifData")) {
                                    t.trigger("ExifData", r, A.EXIF())
                                }
                                if (t.hasEventListener("GpsData")) {
                                    t.trigger("GpsData", r, A.GPS())
                                }
                            }
                        }
                        if (p.quality) {
                            try {
                                w = q.toDataURL(n, p.quality / 100)
                            } catch (C) {
                                w = q.toDataURL(n)
                            }
                        }
                    } else {
                        w = q.toDataURL(n)
                    }
                    w = w.substring(w.indexOf("base64,") + 7);
                    w = atob(w);
                    if (y && y.headers && y.headers.length) {
                        w = y.restore(w);
                        y.purge()
                    }
                    q.parentNode.removeChild(q);
                    v({success: true, data: w})
                } else {
                    v({success: false})
                }
            };
            u.src = w
        })
    }

    j.runtimes.Html5 = j.addRuntime("html5", {getFeatures: function () {
        var s, o, r, q, p, n;
        o = r = p = n = false;
        if (h.XMLHttpRequest) {
            s = new XMLHttpRequest();
            r = !!s.upload;
            o = !!(s.sendAsBinary || s.upload)
        }
        if (o) {
            q = !!(s.sendAsBinary || (h.Uint8Array && h.ArrayBuffer));
            p = !!(File && (File.prototype.getAsDataURL || h.FileReader) && q);
            n = !!(File && (File.prototype.mozSlice || File.prototype.webkitSlice || File.prototype.slice))
        }
        g = j.ua.safari && j.ua.windows;
        return{html5: o, dragdrop: (function () {
            var t = k.createElement("div");
            return("draggable" in t) || ("ondragstart" in t && "ondrop" in t)
        }()), jpgresize: p, pngresize: p, multipart: p || !!h.FileReader || !!h.FormData, canSendBinary: q, cantSendBlobInFormData: !!(j.ua.gecko && h.FormData && h.FileReader && !FileReader.prototype.readAsArrayBuffer), progress: r, chunks: n, multi_selection: !(j.ua.safari && j.ua.windows), triggerDialog: (j.ua.gecko && h.FormData || j.ua.webkit)}
    }, init: function (p, r) {
        var n, q;

        function o(w) {
            var u, t, v = [], x, s = {};
            for (t = 0; t < w.length; t++) {
                u = w[t];
                if (s[u.name]) {
                    continue
                }
                s[u.name] = true;
                x = j.guid();
                c[x] = u;
                v.push(new j.File(x, u.fileName || u.name, u.fileSize || u.size))
            }
            if (v.length) {
                p.trigger("FilesAdded", v)
            }
        }

        n = this.getFeatures();
        if (!n.html5) {
            r({success: false});
            return
        }
        p.bind("Init", function (w) {
            var G, F, C = [], v, D, t = w.settings.filters, u, B, s = k.body, E;
            G = k.createElement("div");
            G.id = w.id + "_html5_container";
            j.extend(G.style, {position: "absolute", background: p.settings.shim_bgcolor || "transparent", width: "100px", height: "100px", overflow: "hidden", zIndex: 99999, opacity: p.settings.shim_bgcolor ? "" : 0});
            G.className = "plupload html5";
            if (p.settings.container) {
                s = k.getElementById(p.settings.container);
                if (j.getStyle(s, "position") === "static") {
                    s.style.position = "relative"
                }
            }
            s.appendChild(G);
            no_type_restriction:for (v = 0; v < t.length; v++) {
                u = t[v].extensions.split(/,/);
                for (D = 0; D < u.length; D++) {
                    if (u[D] === "*") {
                        C = [];
                        break no_type_restriction
                    }
                    B = j.mimeTypes[u[D]];
                    if (B && j.inArray(B, C) === -1) {
                        C.push(B)
                    }
                }
            }
            G.innerHTML = '<input id="' + p.id + '_html5"  style="font-size:999px" type="file" accept="' + C.join(",") + '" ' + (p.settings.multi_selection && p.features.multi_selection ? 'multiple="multiple"' : "") + " />";
            G.scrollTop = 100;
            E = k.getElementById(p.id + "_html5");
            if (w.features.triggerDialog) {
                j.extend(E.style, {position: "absolute", width: "100%", height: "100%"})
            } else {
                j.extend(E.style, {cssFloat: "right", styleFloat: "right"})
            }
            E.onchange = function () {
                o(this.files);
                this.value = ""
            };
            F = k.getElementById(w.settings.browse_button);
            if (F) {
                var z = w.settings.browse_button_hover, A = w.settings.browse_button_active, x = w.features.triggerDialog ? F : G;
                if (z) {
                    j.addEvent(x, "mouseover", function () {
                        j.addClass(F, z)
                    }, w.id);
                    j.addEvent(x, "mouseout", function () {
                        j.removeClass(F, z)
                    }, w.id)
                }
                if (A) {
                    j.addEvent(x, "mousedown", function () {
                        j.addClass(F, A)
                    }, w.id);
                    j.addEvent(k.body, "mouseup", function () {
                        j.removeClass(F, A)
                    }, w.id)
                }
                if (w.features.triggerDialog) {
                    j.addEvent(F, "click", function (H) {
                        var y = k.getElementById(w.id + "_html5");
                        if (y && !y.disabled) {
                            y.click()
                        }
                        H.preventDefault()
                    }, w.id)
                }
            }
        });
        p.bind("PostInit", function () {
            var s = k.getElementById(p.settings.drop_element);
            if (s) {
                if (g) {
                    j.addEvent(s, "dragenter", function (w) {
                        var v, t, u;
                        v = k.getElementById(p.id + "_drop");
                        if (!v) {
                            v = k.createElement("input");
                            v.setAttribute("type", "file");
                            v.setAttribute("id", p.id + "_drop");
                            v.setAttribute("multiple", "multiple");
                            j.addEvent(v, "change", function () {
                                o(this.files);
                                j.removeEvent(v, "change", p.id);
                                v.parentNode.removeChild(v)
                            }, p.id);
                            s.appendChild(v)
                        }
                        t = j.getPos(s, k.getElementById(p.settings.container));
                        u = j.getSize(s);
                        if (j.getStyle(s, "position") === "static") {
                            j.extend(s.style, {position: "relative"})
                        }
                        j.extend(v.style, {position: "absolute", display: "block", top: 0, left: 0, width: u.w + "px", height: u.h + "px", opacity: 0})
                    }, p.id);
                    return
                }
                j.addEvent(s, "dragover", function (t) {
                    t.preventDefault()
                }, p.id);
                j.addEvent(s, "drop", function (u) {
                    var t = u.dataTransfer;
                    if (t && t.files) {
                        o(t.files)
                    }
                    u.preventDefault()
                }, p.id)
            }
        });
        p.bind("Refresh", function (s) {
            var t, u, v, x, w;
            t = k.getElementById(p.settings.browse_button);
            if (t) {
                u = j.getPos(t, k.getElementById(s.settings.container));
                v = j.getSize(t);
                x = k.getElementById(p.id + "_html5_container");
                j.extend(x.style, {top: u.y + "px", left: u.x + "px", width: v.w + "px", height: v.h + "px"});
                if (p.features.triggerDialog) {
                    if (j.getStyle(t, "position") === "static") {
                        j.extend(t.style, {position: "relative"})
                    }
                    w = parseInt(j.getStyle(t, "z-index"), 10);
                    if (isNaN(w)) {
                        w = 0
                    }
                    j.extend(t.style, {zIndex: w});
                    j.extend(x.style, {zIndex: w - 1})
                }
            }
        });
        p.bind("DisableBrowse", function (s, u) {
            var t = k.getElementById(s.id + "_html5");
            if (t) {
                t.disabled = u
            }
        });
        p.bind("CancelUpload", function () {
            if (q && q.abort) {
                q.abort()
            }
        });
        p.bind("UploadFile", function (s, u) {
            var v = s.settings, y, t;

            function x(A, D, z) {
                var B;
                if (File.prototype.slice) {
                    try {
                        A.slice();
                        return A.slice(D, z)
                    } catch (C) {
                        return A.slice(D, z - D)
                    }
                } else {
                    if (B = File.prototype.webkitSlice || File.prototype.mozSlice) {
                        return B.call(A, D, z)
                    } else {
                        return null
                    }
                }
            }

            function w(A) {
                var D = 0, C = 0, z = ("FileReader" in h) ? new FileReader : null;

                function B() {
                    var I, M, K, L, H, J, F, E = s.settings.url;

                    function G(V) {
                        var T = 0, N = "----pluploadboundary" + j.guid(), O, P = "--", U = "\r\n", R = "";
                        q = new XMLHttpRequest;
                        if (q.upload) {
                            q.upload.onprogress = function (W) {
                                u.loaded = Math.min(u.size, C + W.loaded - T);
                                s.trigger("UploadProgress", u)
                            }
                        }
                        q.onreadystatechange = function () {
                            var W, Y;
                            if (q.readyState == 4 && s.state !== j.STOPPED) {
                                try {
                                    W = q.status
                                } catch (X) {
                                    W = 0
                                }
                                if (W >= 400) {
                                    s.trigger("Error", {code: j.HTTP_ERROR, message: j.translate("HTTP Error."), file: u, status: W})
                                } else {
                                    if (K) {
                                        Y = {chunk: D, chunks: K, response: q.responseText, status: W};
                                        s.trigger("ChunkUploaded", u, Y);
                                        C += J;
                                        if (Y.cancelled) {
                                            u.status = j.FAILED;
                                            return
                                        }
                                        u.loaded = Math.min(u.size, (D + 1) * H)
                                    } else {
                                        u.loaded = u.size
                                    }
                                    s.trigger("UploadProgress", u);
                                    V = I = O = R = null;
                                    if (!K || ++D >= K) {
                                        u.status = j.DONE;
                                        s.trigger("FileUploaded", u, {response: q.responseText, status: W})
                                    } else {
                                        B()
                                    }
                                }
                            }
                        };
                        if (s.settings.multipart && n.multipart) {
                            L.name = u.target_name || u.name;
                            q.open("post", E, true);
                            j.each(s.settings.headers, function (X, W) {
                                q.setRequestHeader(W, X)
                            });
                            if (typeof(V) !== "string" && !!h.FormData) {
                                O = new FormData();
                                j.each(j.extend(L, s.settings.multipart_params), function (X, W) {
                                    O.append(W, X)
                                });
                                O.append(s.settings.file_data_name, V);
                                q.send(O);
                                return
                            }
                            if (typeof(V) === "string") {
                                q.setRequestHeader("Content-Type", "multipart/form-data; boundary=" + N);
                                j.each(j.extend(L, s.settings.multipart_params), function (X, W) {
                                    R += P + N + U + 'Content-Disposition: form-data; name="' + W + '"' + U + U;
                                    R += unescape(encodeURIComponent(X)) + U
                                });
                                F = j.mimeTypes[u.name.replace(/^.+\.([^.]+)/, "$1").toLowerCase()] || "application/octet-stream";
                                R += P + N + U + 'Content-Disposition: form-data; name="' + s.settings.file_data_name + '"; filename="' + unescape(encodeURIComponent(u.name)) + '"' + U + "Content-Type: " + F + U + U + V + U + P + N + P + U;
                                T = R.length - V.length;
                                V = R;
                                if (q.sendAsBinary) {
                                    q.sendAsBinary(V)
                                } else {
                                    if (n.canSendBinary) {
                                        var S = new Uint8Array(V.length);
                                        for (var Q = 0; Q < V.length; Q++) {
                                            S[Q] = (V.charCodeAt(Q) & 255)
                                        }
                                        q.send(S.buffer)
                                    }
                                }
                                return
                            }
                        }
                        E = j.buildUrl(s.settings.url, j.extend(L, s.settings.multipart_params));
                        q.open("post", E, true);
                        q.setRequestHeader("Content-Type", "application/octet-stream");
                        j.each(s.settings.headers, function (X, W) {
                            q.setRequestHeader(W, X)
                        });
                        q.send(V)
                    }

                    if (u.status == j.DONE || u.status == j.FAILED || s.state == j.STOPPED) {
                        return
                    }
                    L = {name: u.target_name || u.name};
                    if (v.chunk_size && u.size > v.chunk_size && (n.chunks || typeof(A) == "string")) {
                        H = v.chunk_size;
                        K = Math.ceil(u.size / H);
                        J = Math.min(H, u.size - (D * H));
                        if (typeof(A) == "string") {
                            I = A.substring(D * H, D * H + J)
                        } else {
                            I = x(A, D * H, D * H + J)
                        }
                        L.chunk = D;
                        L.chunks = K
                    } else {
                        J = u.size;
                        I = A
                    }
                    if (s.settings.multipart && n.multipart && typeof(I) !== "string" && z && n.cantSendBlobInFormData && n.chunks && s.settings.chunk_size) {
                        z.onload = function () {
                            G(z.result)
                        };
                        z.readAsBinaryString(I)
                    } else {
                        G(I)
                    }
                }

                B()
            }

            y = c[u.id];
            if (n.jpgresize && s.settings.resize && /\.(png|jpg|jpeg)$/i.test(u.name)) {
                d.call(s, u, s.settings.resize, /\.png$/i.test(u.name) ? "image/png" : "image/jpeg", function (z) {
                    if (z.success) {
                        u.size = z.data.length;
                        w(z.data)
                    } else {
                        if (n.chunks) {
                            w(y)
                        } else {
                            l(y, w)
                        }
                    }
                })
            } else {
                if (!n.chunks && n.jpgresize) {
                    l(y, w)
                } else {
                    w(y)
                }
            }
        });
        p.bind("Destroy", function (s) {
            var u, v, t = k.body, w = {inputContainer: s.id + "_html5_container", inputFile: s.id + "_html5", browseButton: s.settings.browse_button, dropElm: s.settings.drop_element};
            for (u in w) {
                v = k.getElementById(w[u]);
                if (v) {
                    j.removeAllEvents(v, s.id)
                }
            }
            j.removeAllEvents(k.body, s.id);
            if (s.settings.container) {
                t = k.getElementById(s.settings.container)
            }
            t.removeChild(k.getElementById(w.inputContainer))
        });
        r({success: true})
    }});
    function b() {
        var q = false, o;

        function r(t, v) {
            var s = q ? 0 : -8 * (v - 1), w = 0, u;
            for (u = 0; u < v; u++) {
                w |= (o.charCodeAt(t + u) << Math.abs(s + u * 8))
            }
            return w
        }

        function n(u, s, t) {
            var t = arguments.length === 3 ? t : o.length - s - 1;
            o = o.substr(0, s) + u + o.substr(t + s)
        }

        function p(t, u, w) {
            var x = "", s = q ? 0 : -8 * (w - 1), v;
            for (v = 0; v < w; v++) {
                x += String.fromCharCode((u >> Math.abs(s + v * 8)) & 255)
            }
            n(x, t, w)
        }

        return{II: function (s) {
            if (s === e) {
                return q
            } else {
                q = s
            }
        }, init: function (s) {
            q = false;
            o = s
        }, SEGMENT: function (s, u, t) {
            switch (arguments.length) {
                case 1:
                    return o.substr(s, o.length - s - 1);
                case 2:
                    return o.substr(s, u);
                case 3:
                    n(t, s, u);
                    break;
                default:
                    return o
            }
        }, BYTE: function (s) {
            return r(s, 1)
        }, SHORT: function (s) {
            return r(s, 2)
        }, LONG: function (s, t) {
            if (t === e) {
                return r(s, 4)
            } else {
                p(s, t, 4)
            }
        }, SLONG: function (s) {
            var t = r(s, 4);
            return(t > 2147483647 ? t - 4294967296 : t)
        }, STRING: function (s, t) {
            var u = "";
            for (t += s; s < t; s++) {
                u += String.fromCharCode(r(s, 1))
            }
            return u
        }}
    }

    function f(s) {
        var u = {65505: {app: "EXIF", name: "APP1", signature: "Exif\0"}, 65506: {app: "ICC", name: "APP2", signature: "ICC_PROFILE\0"}, 65517: {app: "IPTC", name: "APP13", signature: "Photoshop 3.0\0"}}, t = [], r, n, p = e, q = 0, o;
        r = new b();
        r.init(s);
        if (r.SHORT(0) !== 65496) {
            return
        }
        n = 2;
        o = Math.min(1048576, s.length);
        while (n <= o) {
            p = r.SHORT(n);
            if (p >= 65488 && p <= 65495) {
                n += 2;
                continue
            }
            if (p === 65498 || p === 65497) {
                break
            }
            q = r.SHORT(n + 2) + 2;
            if (u[p] && r.STRING(n + 4, u[p].signature.length) === u[p].signature) {
                t.push({hex: p, app: u[p].app.toUpperCase(), name: u[p].name.toUpperCase(), start: n, length: q, segment: r.SEGMENT(n, q)})
            }
            n += q
        }
        r.init(null);
        return{headers: t, restore: function (y) {
            r.init(y);
            var w = new f(y);
            if (!w.headers) {
                return false
            }
            for (var x = w.headers.length; x > 0; x--) {
                var z = w.headers[x - 1];
                r.SEGMENT(z.start, z.length, "")
            }
            w.purge();
            n = r.SHORT(2) == 65504 ? 4 + r.SHORT(4) : 2;
            for (var x = 0, v = t.length; x < v; x++) {
                r.SEGMENT(n, 0, t[x].segment);
                n += t[x].length
            }
            return r.SEGMENT()
        }, get: function (x) {
            var y = [];
            for (var w = 0, v = t.length; w < v; w++) {
                if (t[w].app === x.toUpperCase()) {
                    y.push(t[w].segment)
                }
            }
            return y
        }, set: function (y, x) {
            var z = [];
            if (typeof(x) === "string") {
                z.push(x)
            } else {
                z = x
            }
            for (var w = ii = 0, v = t.length; w < v; w++) {
                if (t[w].app === y.toUpperCase()) {
                    t[w].segment = z[ii];
                    t[w].length = z[ii].length;
                    ii++
                }
                if (ii >= z.length) {
                    break
                }
            }
        }, purge: function () {
            t = [];
            r.init(null)
        }}
    }

    function a() {
        var q, n, o = {}, t;
        q = new b();
        n = {tiff: {274: "Orientation", 34665: "ExifIFDPointer", 34853: "GPSInfoIFDPointer"}, exif: {36864: "ExifVersion", 40961: "ColorSpace", 40962: "PixelXDimension", 40963: "PixelYDimension", 36867: "DateTimeOriginal", 33434: "ExposureTime", 33437: "FNumber", 34855: "ISOSpeedRatings", 37377: "ShutterSpeedValue", 37378: "ApertureValue", 37383: "MeteringMode", 37384: "LightSource", 37385: "Flash", 41986: "ExposureMode", 41987: "WhiteBalance", 41990: "SceneCaptureType", 41988: "DigitalZoomRatio", 41992: "Contrast", 41993: "Saturation", 41994: "Sharpness"}, gps: {0: "GPSVersionID", 1: "GPSLatitudeRef", 2: "GPSLatitude", 3: "GPSLongitudeRef", 4: "GPSLongitude"}};
        t = {ColorSpace: {1: "sRGB", 0: "Uncalibrated"}, MeteringMode: {0: "Unknown", 1: "Average", 2: "CenterWeightedAverage", 3: "Spot", 4: "MultiSpot", 5: "Pattern", 6: "Partial", 255: "Other"}, LightSource: {1: "Daylight", 2: "Fliorescent", 3: "Tungsten", 4: "Flash", 9: "Fine weather", 10: "Cloudy weather", 11: "Shade", 12: "Daylight fluorescent (D 5700 - 7100K)", 13: "Day white fluorescent (N 4600 -5400K)", 14: "Cool white fluorescent (W 3900 - 4500K)", 15: "White fluorescent (WW 3200 - 3700K)", 17: "Standard light A", 18: "Standard light B", 19: "Standard light C", 20: "D55", 21: "D65", 22: "D75", 23: "D50", 24: "ISO studio tungsten", 255: "Other"}, Flash: {0: "Flash did not fire.", 1: "Flash fired.", 5: "Strobe return light not detected.", 7: "Strobe return light detected.", 9: "Flash fired, compulsory flash mode", 13: "Flash fired, compulsory flash mode, return light not detected", 15: "Flash fired, compulsory flash mode, return light detected", 16: "Flash did not fire, compulsory flash mode", 24: "Flash did not fire, auto mode", 25: "Flash fired, auto mode", 29: "Flash fired, auto mode, return light not detected", 31: "Flash fired, auto mode, return light detected", 32: "No flash function", 65: "Flash fired, red-eye reduction mode", 69: "Flash fired, red-eye reduction mode, return light not detected", 71: "Flash fired, red-eye reduction mode, return light detected", 73: "Flash fired, compulsory flash mode, red-eye reduction mode", 77: "Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected", 79: "Flash fired, compulsory flash mode, red-eye reduction mode, return light detected", 89: "Flash fired, auto mode, red-eye reduction mode", 93: "Flash fired, auto mode, return light not detected, red-eye reduction mode", 95: "Flash fired, auto mode, return light detected, red-eye reduction mode"}, ExposureMode: {0: "Auto exposure", 1: "Manual exposure", 2: "Auto bracket"}, WhiteBalance: {0: "Auto white balance", 1: "Manual white balance"}, SceneCaptureType: {0: "Standard", 1: "Landscape", 2: "Portrait", 3: "Night scene"}, Contrast: {0: "Normal", 1: "Soft", 2: "Hard"}, Saturation: {0: "Normal", 1: "Low saturation", 2: "High saturation"}, Sharpness: {0: "Normal", 1: "Soft", 2: "Hard"}, GPSLatitudeRef: {N: "North latitude", S: "South latitude"}, GPSLongitudeRef: {E: "East longitude", W: "West longitude"}};
        function p(u, C) {
            var w = q.SHORT(u), z, F, G, B, A, v, x, D, E = [], y = {};
            for (z = 0; z < w; z++) {
                x = v = u + 12 * z + 2;
                G = C[q.SHORT(x)];
                if (G === e) {
                    continue
                }
                B = q.SHORT(x += 2);
                A = q.LONG(x += 2);
                x += 4;
                E = [];
                switch (B) {
                    case 1:
                    case 7:
                        if (A > 4) {
                            x = q.LONG(x) + o.tiffHeader
                        }
                        for (F = 0; F < A; F++) {
                            E[F] = q.BYTE(x + F)
                        }
                        break;
                    case 2:
                        if (A > 4) {
                            x = q.LONG(x) + o.tiffHeader
                        }
                        y[G] = q.STRING(x, A - 1);
                        continue;
                    case 3:
                        if (A > 2) {
                            x = q.LONG(x) + o.tiffHeader
                        }
                        for (F = 0; F < A; F++) {
                            E[F] = q.SHORT(x + F * 2)
                        }
                        break;
                    case 4:
                        if (A > 1) {
                            x = q.LONG(x) + o.tiffHeader
                        }
                        for (F = 0; F < A; F++) {
                            E[F] = q.LONG(x + F * 4)
                        }
                        break;
                    case 5:
                        x = q.LONG(x) + o.tiffHeader;
                        for (F = 0; F < A; F++) {
                            E[F] = q.LONG(x + F * 4) / q.LONG(x + F * 4 + 4)
                        }
                        break;
                    case 9:
                        x = q.LONG(x) + o.tiffHeader;
                        for (F = 0; F < A; F++) {
                            E[F] = q.SLONG(x + F * 4)
                        }
                        break;
                    case 10:
                        x = q.LONG(x) + o.tiffHeader;
                        for (F = 0; F < A; F++) {
                            E[F] = q.SLONG(x + F * 4) / q.SLONG(x + F * 4 + 4)
                        }
                        break;
                    default:
                        continue
                }
                D = (A == 1 ? E[0] : E);
                if (t.hasOwnProperty(G) && typeof D != "object") {
                    y[G] = t[G][D]
                } else {
                    y[G] = D
                }
            }
            return y
        }

        function s() {
            var v = e, u = o.tiffHeader;
            q.II(q.SHORT(u) == 18761);
            if (q.SHORT(u += 2) !== 42) {
                return false
            }
            o.IFD0 = o.tiffHeader + q.LONG(u += 2);
            v = p(o.IFD0, n.tiff);
            o.exifIFD = ("ExifIFDPointer" in v ? o.tiffHeader + v.ExifIFDPointer : e);
            o.gpsIFD = ("GPSInfoIFDPointer" in v ? o.tiffHeader + v.GPSInfoIFDPointer : e);
            return true
        }

        function r(w, u, z) {
            var B, y, x, A = 0;
            if (typeof(u) === "string") {
                var v = n[w.toLowerCase()];
                for (hex in v) {
                    if (v[hex] === u) {
                        u = hex;
                        break
                    }
                }
            }
            B = o[w.toLowerCase() + "IFD"];
            y = q.SHORT(B);
            for (i = 0; i < y; i++) {
                x = B + 12 * i + 2;
                if (q.SHORT(x) == u) {
                    A = x + 8;
                    break
                }
            }
            if (!A) {
                return false
            }
            q.LONG(A, z);
            return true
        }

        return{init: function (u) {
            o = {tiffHeader: 10};
            if (u === e || !u.length) {
                return false
            }
            q.init(u);
            if (q.SHORT(0) === 65505 && q.STRING(4, 5).toUpperCase() === "EXIF\0") {
                return s()
            }
            return false
        }, EXIF: function () {
            var v;
            v = p(o.exifIFD, n.exif);
            if (v.ExifVersion && j.typeOf(v.ExifVersion) === "array") {
                for (var w = 0, u = ""; w < v.ExifVersion.length; w++) {
                    u += String.fromCharCode(v.ExifVersion[w])
                }
                v.ExifVersion = u
            }
            return v
        }, GPS: function () {
            var u;
            u = p(o.gpsIFD, n.gps);
            if (u.GPSVersionID) {
                u.GPSVersionID = u.GPSVersionID.join(".")
            }
            return u
        }, setExif: function (u, v) {
            if (u !== "PixelXDimension" && u !== "PixelYDimension") {
                return false
            }
            return r("exif", u, v)
        }, getBinary: function () {
            return q.SEGMENT()
        }}
    }
})(window, document, plupload);
(function (f, b, d, e) {
    var a = {}, g = {};

    function c() {
        var h;
        try {
            h = navigator.plugins["Shockwave Flash"];
            h = h.description
        } catch (j) {
            try {
                h = new ActiveXObject("ShockwaveFlash.ShockwaveFlash").GetVariable("$version")
            } catch (i) {
                h = "0.0"
            }
        }
        h = h.match(/\d+/g);
        return parseFloat(h[0] + "." + h[1])
    }

    d.flash = {trigger: function (j, h, i) {
        setTimeout(function () {
            var m = a[j], l, k;
            if (m) {
                m.trigger("Flash:" + h, i)
            }
        }, 0)
    }};
    d.runtimes.Flash = d.addRuntime("flash", {getFeatures: function () {
        return{jpgresize: true, pngresize: true, maxWidth: 8091, maxHeight: 8091, chunks: true, progress: true, multipart: true, multi_selection: true}
    }, init: function (m, o) {
        var k, l, h = 0, i = b.body;
        if (c() < 10) {
            o({success: false});
            return
        }
        g[m.id] = false;
        a[m.id] = m;
        k = b.getElementById(m.settings.browse_button);
        l = b.createElement("div");
        l.id = m.id + "_flash_container";
        d.extend(l.style, {position: "absolute", top: "0px", background: m.settings.shim_bgcolor || "transparent", zIndex: 99999, width: "100%", height: "100%"});
        l.className = "plupload flash";
        if (m.settings.container) {
            i = b.getElementById(m.settings.container);
            if (d.getStyle(i, "position") === "static") {
                i.style.position = "relative"
            }
        }
        i.appendChild(l);
        (function () {
            var p, q;
            p = '<object id="' + m.id + '_flash" type="application/x-shockwave-flash" data="' + m.settings.flash_swf_url + '" ';
            if (d.ua.ie) {
                p += 'classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" '
            }
            p += 'width="100%" height="100%" style="outline:0"><param name="movie" value="' + m.settings.flash_swf_url + '" /><param name="flashvars" value="id=' + escape(m.id) + '" /><param name="wmode" value="transparent" /><param name="allowscriptaccess" value="always" /></object>';
            if (d.ua.ie) {
                q = b.createElement("div");
                l.appendChild(q);
                q.outerHTML = p;
                q = null
            } else {
                l.innerHTML = p
            }
        }());
        function n() {
            return b.getElementById(m.id + "_flash")
        }

        function j() {
            if (h++ > 5000) {
                o({success: false});
                return
            }
            if (g[m.id] === false) {
                setTimeout(j, 1)
            }
        }

        j();
        k = l = null;
        m.bind("Destroy", function (p) {
            var q;
            d.removeAllEvents(b.body, p.id);
            delete g[p.id];
            delete a[p.id];
            q = b.getElementById(p.id + "_flash_container");
            if (q) {
                i.removeChild(q)
            }
        });
        m.bind("Flash:Init", function () {
            var r = {}, q;
            try {
                n().setFileFilters(m.settings.filters, m.settings.multi_selection)
            } catch (p) {
                o({success: false});
                return
            }
            if (g[m.id]) {
                return
            }
            g[m.id] = true;
            m.bind("UploadFile", function (s, u) {
                var v = s.settings, t = m.settings.resize || {};
                n().uploadFile(r[u.id], v.url, {name: u.target_name || u.name, mime: d.mimeTypes[u.name.replace(/^.+\.([^.]+)/, "$1").toLowerCase()] || "application/octet-stream", chunk_size: v.chunk_size, width: t.width, height: t.height, quality: t.quality, multipart: v.multipart, multipart_params: v.multipart_params || {}, file_data_name: v.file_data_name, format: /\.(jpg|jpeg)$/i.test(u.name) ? "jpg" : "png", headers: v.headers, urlstream_upload: v.urlstream_upload})
            });
            m.bind("CancelUpload", function () {
                n().cancelUpload()
            });
            m.bind("Flash:UploadProcess", function (t, s) {
                var u = t.getFile(r[s.id]);
                if (u.status != d.FAILED) {
                    u.loaded = s.loaded;
                    u.size = s.size;
                    t.trigger("UploadProgress", u)
                }
            });
            m.bind("Flash:UploadChunkComplete", function (s, u) {
                var v, t = s.getFile(r[u.id]);
                v = {chunk: u.chunk, chunks: u.chunks, response: u.text};
                s.trigger("ChunkUploaded", t, v);
                if (t.status !== d.FAILED && s.state !== d.STOPPED) {
                    n().uploadNextChunk()
                }
                if (u.chunk == u.chunks - 1) {
                    t.status = d.DONE;
                    s.trigger("FileUploaded", t, {response: u.text})
                }
            });
            m.bind("Flash:SelectFiles", function (s, v) {
                var u, t, w = [], x;
                for (t = 0; t < v.length; t++) {
                    u = v[t];
                    x = d.guid();
                    r[x] = u.id;
                    r[u.id] = x;
                    w.push(new d.File(x, u.name, u.size))
                }
                if (w.length) {
                    m.trigger("FilesAdded", w)
                }
            });
            m.bind("Flash:SecurityError", function (s, t) {
                m.trigger("Error", {code: d.SECURITY_ERROR, message: d.translate("Security error."), details: t.message, file: m.getFile(r[t.id])})
            });
            m.bind("Flash:GenericError", function (s, t) {
                m.trigger("Error", {code: d.GENERIC_ERROR, message: d.translate("Generic error."), details: t.message, file: m.getFile(r[t.id])})
            });
            m.bind("Flash:IOError", function (s, t) {
                m.trigger("Error", {code: d.IO_ERROR, message: d.translate("IO error."), details: t.message, file: m.getFile(r[t.id])})
            });
            m.bind("Flash:ImageError", function (s, t) {
                m.trigger("Error", {code: parseInt(t.code, 10), message: d.translate("Image error."), file: m.getFile(r[t.id])})
            });
            m.bind("Flash:StageEvent:rollOver", function (s) {
                var t, u;
                t = b.getElementById(m.settings.browse_button);
                u = s.settings.browse_button_hover;
                if (t && u) {
                    d.addClass(t, u)
                }
            });
            m.bind("Flash:StageEvent:rollOut", function (s) {
                var t, u;
                t = b.getElementById(m.settings.browse_button);
                u = s.settings.browse_button_hover;
                if (t && u) {
                    d.removeClass(t, u)
                }
            });
            m.bind("Flash:StageEvent:mouseDown", function (s) {
                var t, u;
                t = b.getElementById(m.settings.browse_button);
                u = s.settings.browse_button_active;
                if (t && u) {
                    d.addClass(t, u);
                    d.addEvent(b.body, "mouseup", function () {
                        d.removeClass(t, u)
                    }, s.id)
                }
            });
            m.bind("Flash:StageEvent:mouseUp", function (s) {
                var t, u;
                t = b.getElementById(m.settings.browse_button);
                u = s.settings.browse_button_active;
                if (t && u) {
                    d.removeClass(t, u)
                }
            });
            m.bind("Flash:ExifData", function (s, t) {
                m.trigger("ExifData", m.getFile(r[t.id]), t.data)
            });
            m.bind("Flash:GpsData", function (s, t) {
                m.trigger("GpsData", m.getFile(r[t.id]), t.data)
            });
            m.bind("QueueChanged", function (s) {
                m.refresh()
            });
            m.bind("FilesRemoved", function (s, u) {
                var t;
                for (t = 0; t < u.length; t++) {
                    n().removeFile(r[u[t].id])
                }
            });
            m.bind("StateChanged", function (s) {
                m.refresh()
            });
            m.bind("Refresh", function (s) {
                var t, u, v;
                n().setFileFilters(m.settings.filters, m.settings.multi_selection);
                t = b.getElementById(s.settings.browse_button);
                if (t) {
                    u = d.getPos(t, b.getElementById(s.settings.container));
                    v = d.getSize(t);
                    d.extend(b.getElementById(s.id + "_flash_container").style, {top: u.y + "px", left: u.x + "px", width: v.w + "px", height: v.h + "px"})
                }
            });
            m.bind("DisableBrowse", function (s, t) {
                n().disableBrowse(t)
            });
            o({success: true})
        })
    }})
})(window, document, plupload);
/*
 Paginator 3000
 - idea by ecto (ecto.ru)
 - coded by karaboz (karaboz.ru)

 How to implement:
 <div class="paginator" id="paginator_example"></div>
 <script type="text/javascript">
 paginator_example = new Paginator('paginator_example', 2048, 10, 1, 'http://www.yourwebsite.com/pages/');
 </script>

 Be sure that width of your paginator does not change after page is loaded
 If it happens you must call Paginator.resizePaginator(paginator_example) function to redraw paginator

 */

/*
 Paginator class
 paginatorHolderId - id of the html element where paginator will be placed as innerHTML (String): required
 pagesTotal - number of pages (Number, required)
 pagesSpan - number of pages which are visible at once (Number, required) 
 pageCurrent - the number of current page (Number, required)
 baseUrl - the url of the website (String)
 if baseUrl is 'http://www.yourwebsite.com/pages/' the links on the pages will be:
 http://www.yourwebsite.com/pages/1, http://www.yourwebsite.com/pages/2,	etc
 */
var Paginator = function (paginatorHolderId, pagesTotal, pagesSpan, pageCurrent, baseUrl, maxPages, controls) {
    if (!document.getElementById(paginatorHolderId) || !pagesTotal || !pagesSpan) return false;

    this.inputData = {
        paginatorHolderId: paginatorHolderId,
        pagesTotal: pagesTotal,
        pagesSpan: pagesSpan < pagesTotal ? pagesSpan : pagesTotal,
        pageCurrent: pageCurrent,
        baseUrl: baseUrl ? baseUrl : '/pages/',
        maxPages: null || maxPages,
        prevControl: controls && controls.prev ? controls.prev : '',
        nextControl: controls && controls.next ? controls.next : ''
    };

    this.html = {
        holder: null,

        table: null,
        trPages: null,
        trScrollBar: null,
        tdsPages: null,

        scrollBar: null,
        scrollThumb: null,

        pageCurrentMark: null
    };


    this.prepareHtml();

    this.initScrollThumb();
    this.initPageCurrentMark();
    this.initEvents();

    this.scrollToPageCurrent();
};

/*
 Set all .html properties (links to dom objects)
 */
Paginator.prototype.prepareHtml = function () {

    this.html.holder = document.getElementById(this.inputData.paginatorHolderId);
    this.html.holder.innerHTML = this.makePagesTableHtml();

    this.html.table = this.html.holder.getElementsByTagName('table')[0];

    var trPages = this.html.table.getElementsByTagName('tr')[0];
    this.html.tdsPages = trPages.getElementsByTagName('td');

    this.html.scrollBar = getElementsByClassName(this.html.table, 'div', 'scroll_bar')[0];
    this.html.scrollThumb = getElementsByClassName(this.html.table, 'div', 'scroll_thumb')[0];
    this.html.pageCurrentMark = getElementsByClassName(this.html.table, 'div', 'current_page_mark')[0];

    // hide scrollThumb if there is no scroll (we see all pages at once)
    if (this.inputData.pagesSpan == this.inputData.pagesTotal) {
        addClass(this.html.holder, 'fullsize');
    }
};

/*
 Make html for pages (table) 
 */
Paginator.prototype.makePagesTableHtml = function () {
    var tdWidth = (100 / this.inputData.pagesSpan) + '%';
    var tableWidth = this.inputData.pagesSpan * 10;
    var minTableWidth = (this.inputData.pagesSpan * 50) / (this.html.holder.offsetWidth * 0.01);

    tableWidth = Math.max(tableWidth, minTableWidth);

    tableWidth = tableWidth > 99 ? 99 : tableWidth;

    $$('.paginator_go_to_pages').set('styles', {
        'width': tableWidth + 1 + '%'
    });
    $(this.html.holder).set('styles', {
        'width': tableWidth + '%'
    });
    var html = '' +
        '<table style="width:100%;">' +
        '<tr>'
    for (var i = 1; i <= this.inputData.pagesSpan; i++) {
        html += '<td width="' + tdWidth + '"></td>';
    }
    html += '' +
        '</tr>' +
        '<tr>' +
        '<td colspan="' + this.inputData.pagesSpan + '">' +
        '<div class="scroll_bar">' +
        '<div class="scroll_trough"></div>' +
        '<div class="scroll_thumb">' +
        '<div class="scroll_knob"></div>' +
        '</div>' +
        '<div class="current_page_mark"></div>' +
        '</div>' +
        '</td>' +
        '</tr>' +
        '</table>';

    return html;
};

/*
 Set all needed properties for scrollThumb and it's width
 */
Paginator.prototype.initScrollThumb = function () {
    this.html.scrollThumb.widthMin = '8'; // minimum width of the scrollThumb (px)
    this.html.scrollThumb.widthPercent = this.inputData.pagesSpan / this.inputData.pagesTotal * 100;

    this.html.scrollThumb.xPosPageCurrent = (this.inputData.pageCurrent - Math.round(this.inputData.pagesSpan / 2)) / this.inputData.pagesTotal * this.html.table.offsetWidth;
    this.html.scrollThumb.xPos = this.html.scrollThumb.xPosPageCurrent;

    this.html.scrollThumb.xPosMin = 0;
    this.html.scrollThumb.xPosMax;

    this.html.scrollThumb.widthActual;

    this.setScrollThumbWidth();

};

Paginator.prototype.setScrollThumbWidth = function () {
    // Try to set width in percents
    this.html.scrollThumb.style.width = this.html.scrollThumb.widthPercent + "%";

    // Fix the actual width in px
    this.html.scrollThumb.widthActual = this.html.scrollThumb.offsetWidth;

    // If actual width less then minimum which we set
    if (this.html.scrollThumb.widthActual < this.html.scrollThumb.widthMin) {
        this.html.scrollThumb.style.width = this.html.scrollThumb.widthMin + 'px';
    }

    this.html.scrollThumb.xPosMax = this.html.table.offsetWidth - this.html.scrollThumb.widthActual;
};

Paginator.prototype.moveScrollThumb = function () {
    this.html.scrollThumb.style.left = this.html.scrollThumb.xPos + "px";
};


/*
 Set all needed properties for pageCurrentMark, it's width and move it
 */
Paginator.prototype.initPageCurrentMark = function () {
    this.html.pageCurrentMark.widthMin = '3';
    this.html.pageCurrentMark.widthPercent = 100 / this.inputData.pagesTotal;
    this.html.pageCurrentMark.widthActual;

    this.setPageCurrentPointWidth();
    this.movePageCurrentPoint();
};

Paginator.prototype.setPageCurrentPointWidth = function () {
    // Try to set width in percents
    this.html.pageCurrentMark.style.width = this.html.pageCurrentMark.widthPercent + '%';

    // Fix the actual width in px
    this.html.pageCurrentMark.widthActual = this.html.pageCurrentMark.offsetWidth;

    // If actual width less then minimum which we set
    if (this.html.pageCurrentMark.widthActual < this.html.pageCurrentMark.widthMin) {
        this.html.pageCurrentMark.style.width = this.html.pageCurrentMark.widthMin + 'px';
    }
};

Paginator.prototype.movePageCurrentPoint = function () {
    var pageCurrent = this.inputData.pageCurrent > this.inputData.pagesTotal ? this.inputData.pagesTotal : this.inputData.pageCurrent;
    if (this.html.pageCurrentMark.widthActual < this.html.pageCurrentMark.offsetWidth) {
        this.html.pageCurrentMark.style.left = (pageCurrent - 1) / this.inputData.pagesTotal * this.html.table.offsetWidth - this.html.pageCurrentMark.offsetWidth / 2 + "px";
    } else {
        this.html.pageCurrentMark.style.left = (pageCurrent - 1) / this.inputData.pagesTotal * this.html.table.offsetWidth + "px";
    }
};


/*
 Drag, click and resize events
 */
Paginator.prototype.initEvents = function () {
    var _this = this;

    this.html.scrollThumb.onmousedown = function (e) {
        if (!e) var e = window.event;
        e.cancelBubble = true;
        if (e.stopPropagation) e.stopPropagation();

        var dx = getMousePosition(e).x - this.xPos;
        document.onmousemove = function (e) {
            if (!e) var e = window.event;
            _this.html.scrollThumb.xPos = getMousePosition(e).x - dx;

            // the first: draw pages, the second: move scrollThumb (it was logically but ie sucks!)
            _this.moveScrollThumb();
            _this.drawPages();


        }
        document.onmouseup = function () {
            document.onmousemove = null;
            _this.enableSelection();
        }
        _this.disableSelection();
    }

    this.html.scrollBar.onmousedown = function (e) {
        if (!e) var e = window.event;
        if (matchClass(_this.paginatorBox, 'fullsize')) return;

        _this.html.scrollThumb.xPos = getMousePosition(e).x - getPageX(_this.html.scrollBar) - _this.html.scrollThumb.offsetWidth / 2;

        _this.moveScrollThumb();
        _this.drawPages();


    }

    // Comment the row beneath if you set paginator width fixed
    addEventP(window, 'resize', function () {
        Paginator.resizePaginator(_this)
    });
};

/*
 Redraw current span of pages
 */
Paginator.prototype.drawPages = function () {
    var percentFromLeft = this.html.scrollThumb.xPos / (this.html.table.offsetWidth);
    var cellFirstValue = Math.round(percentFromLeft * this.inputData.pagesTotal);

    var html = "";
    // drawing pages control the position of the scrollThumb on the edges!
    if (cellFirstValue < 1) {
        cellFirstValue = 1;
        this.html.scrollThumb.xPos = 0;
        this.moveScrollThumb();
    } else if (cellFirstValue >= this.inputData.pagesTotal - this.inputData.pagesSpan) {
        cellFirstValue = this.inputData.pagesTotal - this.inputData.pagesSpan + 1;
        this.html.scrollThumb.xPos = this.html.table.offsetWidth - this.html.scrollThumb.offsetWidth;
        this.moveScrollThumb();
    }


    for (var i = 0; i < this.html.tdsPages.length; i++) {
        var cellCurrentValue = cellFirstValue + i;
        if (cellCurrentValue == this.inputData.pageCurrent) {
            html = "<span>" + "<strong><em>" + cellCurrentValue + "</em></strong>" + "</span>";
        } else {
            if (this.inputData.maxPages && cellCurrentValue > this.inputData.maxPages) {
                var
                    html = "<span>" + "<a href='/archive'><em>архив</em></a>" + "</span>";
            } else {
                html = "<span>" + "<a href='" + this.inputData.baseUrl + cellCurrentValue + "'><em>" + cellCurrentValue + "</em></a>" + "</span>";
            }
        }
        this.html.tdsPages[i].innerHTML = html;
    }
};

/*
 Scroll to current page
 */
Paginator.prototype.scrollToPageCurrent = function () {
    this.html.scrollThumb.xPosPageCurrent = (this.inputData.pageCurrent - Math.round(this.inputData.pagesSpan / 2)) / this.inputData.pagesTotal * this.html.table.offsetWidth;
    this.html.scrollThumb.xPos = this.html.scrollThumb.xPosPageCurrent;

    this.moveScrollThumb();
    this.drawPages();

};


Paginator.prototype.disableSelection = function () {
    document.onselectstart = function () {
        return false;
    }
    this.html.scrollThumb.focus();
};

Paginator.prototype.enableSelection = function () {
    document.onselectstart = function () {
        return true;
    }
};

/*
 Function is used when paginator was resized (window.onresize fires it automatically)
 Use it when you change paginator with DHTML
 Do not use it if you set fixed width of paginator
 */
Paginator.resizePaginator = function (paginatorObj) {

    paginatorObj.setPageCurrentPointWidth();
    paginatorObj.movePageCurrentPoint();

    paginatorObj.setScrollThumbWidth();
    paginatorObj.scrollToPageCurrent();
};

Paginator.prototype.redraw = function (paginatorObj) {
    this.prepareHtml();

    this.initScrollThumb();
    this.initPageCurrentMark();
    this.initEvents();

    this.scrollToPageCurrent();
    this.updateControls();
};

Paginator.prototype.updateControls = function () {
    if (this.inputData.nextControl) {
        if (this.inputData.pageCurrent == this.inputData.maxPages) {
            this.inputData.nextControl.href = '/archive';
        } else {
            if (this.inputData.pageCurrent == this.inputData.pagesTotal) {
                this.inputData.nextControl.addClass('hidden');
            } else {
                this.inputData.nextControl.href = this.inputData.baseUrl + (this.inputData.pageCurrent + 1);
            }
        }
    }
    if (this.inputData.prevControl) {
        this.inputData.prevControl.href = this.inputData.baseUrl + (this.inputData.pageCurrent - 1);
    }

    if (this.inputData.pageCurrent > 1) {
        this.inputData.prevControl.removeClass('hidden');
    }
};


/*
 Global functions which are used
 */
function getElementsByClassName(objParentNode, strNodeName, strClassName) {
    var nodes = objParentNode.getElementsByTagName(strNodeName);
    if (!strClassName) {
        return nodes;
    }
    var nodesWithClassName = [];
    for (var i = 0; i < nodes.length; i++) {
        if (matchClass(nodes[i], strClassName)) {
            nodesWithClassName[nodesWithClassName.length] = nodes[i];
        }
    }
    return nodesWithClassName;
};


function addClass(objNode, strNewClass) {
    replaceClass(objNode, strNewClass, '');
};

function removeClass(objNode, strCurrClass) {
    replaceClass(objNode, '', strCurrClass);
};

function replaceClass(objNode, strNewClass, strCurrClass) {
    var strOldClass = strNewClass;
    if (strCurrClass && strCurrClass.length) {
        strCurrClass = strCurrClass.replace(/\s+(\S)/g, '|$1');
        if (strOldClass.length) strOldClass += '|';
        strOldClass += strCurrClass;
    }
    objNode.className = objNode.className.replace(new RegExp('(^|\\s+)(' + strOldClass + ')($|\\s+)', 'g'), '$1');
    objNode.className += ( (objNode.className.length) ? ' ' : '' ) + strNewClass;
};

function matchClass(objNode, strCurrClass) {
    return ( objNode && objNode.className.length && objNode.className.match(new RegExp('(^|\\s+)(' + strCurrClass + ')($|\\s+)')) );
};


function addEventP(objElement, strEventType, ptrEventFunc) {
    if (objElement.addEventListener)
        objElement.addEventListener(strEventType, ptrEventFunc, false);
    else if (objElement.attachEvent)
        objElement.attachEvent('on' + strEventType, ptrEventFunc);
};

function getPageY(oElement) {
    var iPosY = oElement.offsetTop;
    while (oElement.offsetParent != null) {
        oElement = oElement.offsetParent;
        iPosY += oElement.offsetTop;
        if (oElement.tagName == 'BODY') break;
    }
    return iPosY;
};

function getPageX(oElement) {
    var iPosX = oElement.offsetLeft;
    while (oElement.offsetParent != null) {
        oElement = oElement.offsetParent;
        iPosX += oElement.offsetLeft;
        if (oElement.tagName == 'BODY') break;
    }
    return iPosX;
};

function getMousePosition(e) {
    if (e.pageX || e.pageY) {
        var posX = e.pageX;
        var posY = e.pageY;
    } else if (e.clientX || e.clientY) {
        var posX = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        var posY = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }
    return {x: posX, y: posY}
};
/*
 *  Copyright 2012-2013 (c) Pierre Duquesne <stackp@online.fr>
 *  Licensed under the New BSD License.
 *  https://github.com/stackp/promisejs
 */
(function (a) {
    function b(a, b) {
        return function () {
            return a.apply(b, arguments);
        };
    }

    function c() {
        this._callbacks = [];
    }

    c.prototype.then = function (a, c) {
        var d = b(a, c);
        if (this._isdone)d(this.error, this.result); else this._callbacks.push(d);
    };
    c.prototype.done = function (a, b) {
        this._isdone = true;
        this.error = a;
        this.result = b;
        for (var c = 0; c < this._callbacks.length; c++)this._callbacks[c](a, b);
        this._callbacks = [];
    };
    function d(a) {
        var b = a.length;
        var d = 0;
        var e = new c();
        var f = [];
        var g = [];

        function h(a) {
            return function (c, h) {
                d += 1;
                f[a] = c;
                g[a] = h;
                if (d === b)e.done(f, g);
            };
        }

        for (var i = 0; i < b; i++)a[i]().then(h(i));
        return e;
    }

    function e(a, b, d) {
        var f = new c();
        if (a.length === 0)f.done(b, d); else a[0](b, d).then(function (b, c) {
            a.splice(0, 1);
            e(a, b, c).then(function (a, b) {
                f.done(a, b);
            });
        });
        return f;
    }

    function f(a) {
        var b = "";
        if (typeof a === "string")b = a; else {
            var c = encodeURIComponent;
            for (var d in a)if (a.hasOwnProperty(d))b += '&' + c(d) + '=' + c(a[d]);
        }
        return b;
    }

    function g() {
        var a;
        if (window.XMLHttpRequest)a = new XMLHttpRequest(); else if (window.ActiveXObject)try {
            a = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (b) {
            a = new ActiveXObject("Microsoft.XMLHTTP");
        }
        return a;
    }

    function h(b, d, e, h) {
        var i = new c();
        var j, k;
        e = e || {};
        h = h || {};
        try {
            j = g();
        } catch (l) {
            i.done(-1, "");
            return i;
        }
        k = f(e);
        if (b === 'GET' && k) {
            d += '?' + k;
            k = null;
        }
        j.open(b, d);
        j.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        for (var m in h)if (h.hasOwnProperty(m))j.setRequestHeader(m, h[m]);
        function n() {
            j.abort();
            i.done(a.promise.ETIMEOUT, "");
        };
        var o = a.promise.ajaxTimeout;
        if (o)var p = setTimeout(n, o);
        j.onreadystatechange = function () {
            if (o)clearTimeout(p);
            if (j.readyState === 4)if (j.status === 200)i.done(null, j.responseText); else i.done(j.status, "");
        };
        j.send(k);
        return i;
    }

    function i(a) {
        return function (b, c, d) {
            return h(a, b, c, d);
        };
    }

    var j = {Promise: c, join: d, chain: e, ajax: h, get: i('GET'), post: i('POST'), put: i('PUT'), del: i('DELETE'), ENOXHR: 1, ETIMEOUT: 2, ajaxTimeout: 0};
    if (typeof define === 'function' && define.amd)define(function () {
        return j;
    }); else a.promise = j;
})(this);
var RecaptchaTemplates = {};
RecaptchaTemplates.VertHtml = '<table id="recaptcha_table" class="recaptchatable" > <tr> <td colspan="6" class=\'recaptcha_r1_c1\'></td> </tr> <tr> <td class=\'recaptcha_r2_c1\'></td> <td colspan="4" class=\'recaptcha_image_cell\'><div id="recaptcha_image"></div></td> <td class=\'recaptcha_r2_c2\'></td> </tr> <tr> <td rowspan="6" class=\'recaptcha_r3_c1\'></td> <td colspan="4" class=\'recaptcha_r3_c2\'></td> <td rowspan="6" class=\'recaptcha_r3_c3\'></td> </tr> <tr> <td rowspan="3" class=\'recaptcha_r4_c1\' height="49"> <div class="recaptcha_input_area"> <input name="recaptcha_response_field" id="recaptcha_response_field" type="text" autocorrect="off" autocapitalize="off" placeholder="" /> <span id="recaptcha_privacy" class="recaptcha_only_if_privacy"></span> </div> </td> <td rowspan="4" class=\'recaptcha_r4_c2\'></td> <td><a id=\'recaptcha_reload_btn\'><img id=\'recaptcha_reload\' width="25" height="17" /></a></td> <td rowspan="4" class=\'recaptcha_r4_c4\'></td> </tr> <tr> <td><a id=\'recaptcha_switch_audio_btn\' class="recaptcha_only_if_image"><img id=\'recaptcha_switch_audio\' width="25" height="16" alt="" /></a><a id=\'recaptcha_switch_img_btn\' class="recaptcha_only_if_audio"><img id=\'recaptcha_switch_img\' width="25" height="16" alt=""/></a></td> </tr> <tr> <td><a id=\'recaptcha_whatsthis_btn\'><img id=\'recaptcha_whatsthis\' width="25" height="16" /></a></td> </tr> <tr> <td class=\'recaptcha_r7_c1\'></td> <td class=\'recaptcha_r8_c1\'></td> </tr> </table> ';
RecaptchaTemplates.CleanCss = ".recaptchatable td img{display:block}.recaptchatable .recaptcha_image_cell center img{height:57px}.recaptchatable .recaptcha_image_cell center{height:57px}.recaptchatable .recaptcha_image_cell{background-color:white;height:57px;padding:7px!important}.recaptchatable,#recaptcha_area tr,#recaptcha_area td,#recaptcha_area th{margin:0!important;border:0!important;border-collapse:collapse!important;vertical-align:middle!important}.recaptchatable *{margin:0;padding:0;border:0;color:black;position:static;top:auto;left:auto;right:auto;bottom:auto}.recaptchatable #recaptcha_image{margin:auto;border:1px solid #dfdfdf!important}.recaptchatable a img{border:0}.recaptchatable a,.recaptchatable a:hover{cursor:pointer;outline:none;border:0!important;padding:0!important;text-decoration:none;color:blue;background:none!important;font-weight:normal}.recaptcha_input_area{position:relative!important;background:none!important}.recaptchatable label.recaptcha_input_area_text{border:1px solid #dfdfdf!important;margin:0!important;padding:0!important;position:static!important;top:auto!important;left:auto!important;right:auto!important;bottom:auto!important}.recaptcha_theme_red label.recaptcha_input_area_text,.recaptcha_theme_white label.recaptcha_input_area_text{color:black!important}.recaptcha_theme_blackglass label.recaptcha_input_area_text{color:white!important}.recaptchatable #recaptcha_response_field{font-size:11pt}.recaptcha_theme_blackglass #recaptcha_response_field,.recaptcha_theme_white #recaptcha_response_field{border:1px solid gray}.recaptcha_theme_red #recaptcha_response_field{border:1px solid #cca940}.recaptcha_audio_cant_hear_link{font-size:7pt;color:black}.recaptchatable{line-height:1em;border:1px solid #dfdfdf!important}.recaptcha_error_text{color:red}.recaptcha_only_if_privacy{float:right;text-align:right;margin-right:7px}";
RecaptchaTemplates.CleanHtml = '<table id="recaptcha_table" class="recaptchatable"> <tr height="73"> <td class=\'recaptcha_image_cell\' width="302"><center><div id="recaptcha_image"></div></center></td> <td style="padding: 10px 7px 7px 7px;"> <a id=\'recaptcha_reload_btn\'><img id=\'recaptcha_reload\' width="25" height="18" alt="" /></a> <a id=\'recaptcha_switch_audio_btn\' class="recaptcha_only_if_image"><img id=\'recaptcha_switch_audio\' width="25" height="15" alt="" /></a><a id=\'recaptcha_switch_img_btn\' class="recaptcha_only_if_audio"><img id=\'recaptcha_switch_img\' width="25" height="15" alt=""/></a> <a id=\'recaptcha_whatsthis_btn\'><img id=\'recaptcha_whatsthis\' width="25" height="16" /></a> </td> <td style="padding: 18px 7px 18px 7px;"> <img id=\'recaptcha_logo\' alt="" width="71" height="36" /> </td> </tr> <tr> <td style="padding-left: 7px;"> <div class="recaptcha_input_area" style="padding-top: 2px; padding-bottom: 7px;"> <input style="border: 1px solid #3c3c3c; width: 302px;" name="recaptcha_response_field" id="recaptcha_response_field" type="text" /> </div> </td> <td colspan=2><span id="recaptcha_privacy" class="recaptcha_only_if_privacy"></span></td> </tr> </table> ';
RecaptchaTemplates.ContextHtml = '<table id="recaptcha_table" class="recaptchatable"> <tr> <td colspan="6" class=\'recaptcha_r1_c1\'></td> </tr> <tr> <td class=\'recaptcha_r2_c1\'></td> <td colspan="4" class=\'recaptcha_image_cell\'><div id="recaptcha_image"></div></td> <td class=\'recaptcha_r2_c2\'></td> </tr> <tr> <td rowspan="6" class=\'recaptcha_r3_c1\'></td> <td colspan="4" class=\'recaptcha_r3_c2\'></td> <td rowspan="6" class=\'recaptcha_r3_c3\'></td> </tr> <tr> <td rowspan="3" class=\'recaptcha_r4_c1\' height="49"> <div class="recaptcha_input_area"> <label for="recaptcha_response_field" class="recaptcha_input_area_text"><span id="recaptcha_instructions_context" class="recaptcha_only_if_image recaptcha_only_if_no_incorrect_sol"></span><span id="recaptcha_instructions_audio" class="recaptcha_only_if_no_incorrect_sol recaptcha_only_if_audio"></span><span id="recaptcha_instructions_error" class="recaptcha_only_if_incorrect_sol"></span></label><br/> <input name="recaptcha_response_field" id="recaptcha_response_field" type="text" /> </div> </td> <td rowspan="4" class=\'recaptcha_r4_c2\'></td> <td><a id=\'recaptcha_reload_btn\'><img id=\'recaptcha_reload\' width="25" height="17" /></a></td> <td rowspan="4" class=\'recaptcha_r4_c4\'></td> </tr> <tr> <td><a id=\'recaptcha_switch_audio_btn\' class="recaptcha_only_if_image"><img id=\'recaptcha_switch_audio\' width="25" height="16" alt="" /></a><a id=\'recaptcha_switch_img_btn\' class="recaptcha_only_if_audio"><img id=\'recaptcha_switch_img\' width="25" height="16" alt=""/></a></td> </tr> <tr> <td><a id=\'recaptcha_whatsthis_btn\'><img id=\'recaptcha_whatsthis\' width="25" height="16" /></a></td> </tr> <tr> <td class=\'recaptcha_r7_c1\'></td> <td class=\'recaptcha_r8_c1\'></td> </tr> </table> ';
RecaptchaTemplates.VertCss = ".recaptchatable td img{display:block}.recaptchatable .recaptcha_r1_c1{background:url('IMGROOT/sprite.png') 0 -63px no-repeat;width:318px;height:9px}.recaptchatable .recaptcha_r2_c1{background:url('IMGROOT/sprite.png') -18px 0 no-repeat;width:9px;height:57px}.recaptchatable .recaptcha_r2_c2{background:url('IMGROOT/sprite.png') -27px 0 no-repeat;width:9px;height:57px}.recaptchatable .recaptcha_r3_c1{background:url('IMGROOT/sprite.png') 0 0 no-repeat;width:9px;height:63px}.recaptchatable .recaptcha_r3_c2{background:url('IMGROOT/sprite.png') -18px -57px no-repeat;width:300px;height:6px}.recaptchatable .recaptcha_r3_c3{background:url('IMGROOT/sprite.png') -9px 0 no-repeat;width:9px;height:63px}.recaptchatable .recaptcha_r4_c1{background:url('IMGROOT/sprite.png') -43px 0 no-repeat;width:171px;height:49px}.recaptchatable .recaptcha_r4_c2{background:url('IMGROOT/sprite.png') -36px 0 no-repeat;width:7px;height:57px}.recaptchatable .recaptcha_r4_c4{background:url('IMGROOT/sprite.png') -214px 0 no-repeat;width:97px;height:57px}.recaptchatable .recaptcha_r7_c1{background:url('IMGROOT/sprite.png') -43px -49px no-repeat;width:171px;height:8px}.recaptchatable .recaptcha_r8_c1{background:url('IMGROOT/sprite.png') -43px -49px no-repeat;width:25px;height:8px}.recaptchatable .recaptcha_image_cell center img{height:57px}.recaptchatable .recaptcha_image_cell center{height:57px}.recaptchatable .recaptcha_image_cell{background-color:white;height:57px}#recaptcha_area,#recaptcha_table{width:318px!important}.recaptchatable,#recaptcha_area tr,#recaptcha_area td,#recaptcha_area th{margin:0!important;border:0!important;padding:0!important;border-collapse:collapse!important;vertical-align:middle!important}.recaptchatable *{margin:0;padding:0;border:0;font-family:helvetica,sans-serif;font-size:8pt;color:black;position:static;top:auto;left:auto;right:auto;bottom:auto}.recaptchatable #recaptcha_image{margin:auto}.recaptchatable img{border:0!important;margin:0!important;padding:0!important}.recaptchatable a,.recaptchatable a:hover{cursor:pointer;outline:none;border:0!important;padding:0!important;text-decoration:none;color:blue;background:none!important;font-weight:normal}.recaptcha_input_area{position:relative!important;width:153px!important;height:45px!important;margin-left:7px!important;margin-right:7px!important;background:none!important}.recaptchatable label.recaptcha_input_area_text{margin:0!important;padding:0!important;position:static!important;top:auto!important;left:auto!important;right:auto!important;bottom:auto!important;background:none!important;height:auto!important;width:auto!important}.recaptcha_theme_red label.recaptcha_input_area_text,.recaptcha_theme_white label.recaptcha_input_area_text{color:black!important}.recaptcha_theme_blackglass label.recaptcha_input_area_text{color:white!important}.recaptchatable #recaptcha_response_field{width:153px!important;position:relative!important;bottom:7px!important;padding:0!important;margin:15px 0 0 0!important;font-size:10pt}.recaptcha_theme_blackglass #recaptcha_response_field,.recaptcha_theme_white #recaptcha_response_field{border:1px solid gray}.recaptcha_theme_red #recaptcha_response_field{border:1px solid #cca940}.recaptcha_audio_cant_hear_link{font-size:7pt;color:black}#recaptcha_instructions_error{color:red!important}.recaptcha_only_if_privacy{float:right;text-align:right}";
var RecaptchaStr_en = {visual_challenge: "Get a visual challenge", audio_challenge: "Get an audio challenge", refresh_btn: "Get a new challenge", instructions_visual: "Type the two words:", instructions_context: "Type the words in the boxes:", instructions_audio: "Type what you hear:", help_btn: "Help", play_again: "Play sound again", cant_hear_this: "Download sound as MP3", incorrect_try_again: "Incorrect. Try again.", image_alt_text: "reCAPTCHA challenge image", privacy_and_terms: "Privacy & Terms"}, RecaptchaStr_af = {visual_challenge: "Kry 'n visuele verifi\u00ebring",
        audio_challenge: "Kry 'n klankverifi\u00ebring", refresh_btn: "Kry 'n nuwe verifi\u00ebring", instructions_visual: "Tik die twee woorde:", instructions_context: "Tik die woorde in die kassies:", instructions_audio: "Tik wat jy hoor:", help_btn: "Hulp", play_again: "Speel geluid weer", cant_hear_this: "Laai die klank af as MP3", incorrect_try_again: "Verkeerd. Probeer weer.", image_alt_text: "reCAPTCHA-uitdagingprent", privacy_and_terms: "Privaatheid en bepalings"}, RecaptchaStr_am = {visual_challenge: "\u12e8\u12a5\u12ed\u1273 \u1270\u130b\u1323\u121a \u12a0\u130d\u129d",
        audio_challenge: "\u120c\u120b \u12a0\u12f2\u1235 \u12e8\u12f5\u121d\u133d \u1325\u12eb\u1244 \u12ed\u1245\u1228\u1265", refresh_btn: "\u120c\u120b \u12a0\u12f2\u1235 \u1325\u12eb\u1244 \u12ed\u1245\u1228\u1265", instructions_visual: "\u12a5\u1295\u12da\u1205\u1295 \u1201\u1208\u1275 \u1243\u120b\u1275 \u1270\u12ed\u1265 \u1366", instructions_context: "\u1260\u1233\u1325\u1296\u1279 \u12cd\u1235\u1325 \u1243\u120b\u1276\u1279\u1295 \u1270\u12ed\u1265\u1366", instructions_audio: "\u12e8\u121d\u1275\u1230\u121b\u12cd\u1295 \u1270\u12ed\u1265\u1361-",
        help_btn: "\u12a5\u1308\u12db", play_again: "\u12f5\u121d\u1339\u1295 \u12a5\u1295\u12f0\u1308\u1293 \u12a0\u132b\u12cd\u1275", cant_hear_this: "\u12f5\u121d\u1339\u1295 \u1260MP3 \u1245\u122d\u133d \u12a0\u12cd\u122d\u12f5", incorrect_try_again: "\u1275\u12ad\u12ad\u120d \u12a0\u12ed\u12f0\u1208\u121d\u1362 \u12a5\u1295\u12f0\u1308\u1293 \u121e\u12ad\u122d\u1362", image_alt_text: "reCAPTCHA \u121d\u1235\u120d \u130d\u1320\u121d", privacy_and_terms: "\u130d\u120b\u12ca\u1290\u1275 \u12a5\u1293 \u12cd\u120d"},
    RecaptchaStr_ar = {visual_challenge: "\u0627\u0644\u062d\u0635\u0648\u0644 \u0639\u0644\u0649 \u062a\u062d\u062f\u064d \u0645\u0631\u0626\u064a", audio_challenge: "\u0627\u0644\u062d\u0635\u0648\u0644 \u0639\u0644\u0649 \u062a\u062d\u062f\u064d \u0635\u0648\u062a\u064a", refresh_btn: "\u0627\u0644\u062d\u0635\u0648\u0644 \u0639\u0644\u0649 \u062a\u062d\u062f\u064d \u062c\u062f\u064a\u062f", instructions_visual: "\u0627\u0643\u062a\u0628 \u0627\u0644\u0643\u0644\u0645\u062a\u064a\u0646:", instructions_context: "\u0627\u0643\u062a\u0628 \u0627\u0644\u0643\u0644\u0645\u0627\u062a \u0641\u064a \u0627\u0644\u0645\u0631\u0628\u0639\u0627\u062a:",
        instructions_audio: "\u0627\u0643\u062a\u0628 \u0645\u0627 \u062a\u0633\u0645\u0639\u0647:", help_btn: "\u0645\u0633\u0627\u0639\u062f\u0629", play_again: "\u062a\u0634\u063a\u064a\u0644 \u0627\u0644\u0635\u0648\u062a \u0645\u0631\u0629 \u0623\u062e\u0631\u0649", cant_hear_this: "\u062a\u0646\u0632\u064a\u0644 \u0627\u0644\u0635\u0648\u062a \u0628\u062a\u0646\u0633\u064a\u0642 MP3", incorrect_try_again: "\u063a\u064a\u0631 \u0635\u062d\u064a\u062d. \u0623\u0639\u062f \u0627\u0644\u0645\u062d\u0627\u0648\u0644\u0629.",
        image_alt_text: "\u0635\u0648\u0631\u0629 \u0627\u0644\u062a\u062d\u062f\u064a \u0645\u0646 reCAPTCHA", privacy_and_terms: "\u0627\u0644\u062e\u0635\u0648\u0635\u064a\u0629 \u0648\u0627\u0644\u0628\u0646\u0648\u062f"}, RecaptchaStr_bg = {visual_challenge: "\u041f\u043e\u043b\u0443\u0447\u0430\u0432\u0430\u043d\u0435 \u043d\u0430 \u0432\u0438\u0437\u0443\u0430\u043b\u043d\u0430 \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0430", audio_challenge: "\u0417\u0430\u0440\u0435\u0436\u0434\u0430\u043d\u0435 \u043d\u0430 \u0430\u0443\u0434\u0438\u043e\u0442\u0435\u0441\u0442",
        refresh_btn: "\u0417\u0430\u0440\u0435\u0436\u0434\u0430\u043d\u0435 \u043d\u0430 \u043d\u043e\u0432 \u0442\u0435\u0441\u0442", instructions_visual: "\u0412\u044a\u0432\u0435\u0434\u0435\u0442\u0435 \u0434\u0432\u0435\u0442\u0435 \u0434\u0443\u043c\u0438:", instructions_context: "\u0412\u044a\u0432\u0435\u0434\u0435\u0442\u0435 \u0434\u0443\u043c\u0438\u0442\u0435:", instructions_audio: "\u0412\u044a\u0432\u0435\u0434\u0435\u0442\u0435 \u0447\u0443\u0442\u043e\u0442\u043e:", help_btn: "\u041f\u043e\u043c\u043e\u0449",
        play_again: "\u041f\u043e\u0432\u0442\u043e\u0440\u043d\u043e \u043f\u0443\u0441\u043a\u0430\u043d\u0435 \u043d\u0430 \u0437\u0432\u0443\u043a\u0430", cant_hear_this: "\u0418\u0437\u0442\u0435\u0433\u043b\u044f\u043d\u0435 \u043d\u0430 \u0437\u0432\u0443\u043a\u0430 \u0432\u044a\u0432 \u0444\u043e\u0440\u043c\u0430\u0442 MP3", incorrect_try_again: "\u041d\u0435\u043f\u0440\u0430\u0432\u0438\u043b\u043d\u043e. \u041e\u043f\u0438\u0442\u0430\u0439\u0442\u0435 \u043e\u0442\u043d\u043e\u0432\u043e.", image_alt_text: "\u0418\u0437\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u0438\u0435 \u043d\u0430 \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0430\u0442\u0430 \u0441 reCAPTCHA",
        privacy_and_terms: "\u041f\u043e\u0432\u0435\u0440\u0438\u0442\u0435\u043b\u043d\u043e\u0441\u0442 \u0438 \u041e\u0431\u0449\u0438 \u0443\u0441\u043b\u043e\u0432\u0438\u044f"}, RecaptchaStr_bn = {visual_challenge: "\u098f\u0995\u099f\u09bf \u09a6\u09c3\u09b6\u09cd\u09af\u09ae\u09be\u09a8 \u09aa\u09cd\u09b0\u09a4\u09bf\u09a6\u09cd\u09ac\u09a8\u09cd\u09a6\u09cd\u09ac\u09bf\u09a4\u09be \u09aa\u09be\u09a8", audio_challenge: "\u098f\u0995\u099f\u09bf \u0985\u09a1\u09bf\u0993 \u09aa\u09cd\u09b0\u09a4\u09bf\u09a6\u09cd\u09ac\u09a8\u09cd\u09a6\u09cd\u09ac\u09bf\u09a4\u09be  \u09aa\u09be\u09a8",
        refresh_btn: "\u098f\u0995\u099f\u09bf \u09a8\u09a4\u09c1\u09a8 \u09aa\u09cd\u09b0\u09a4\u09bf\u09a6\u09cd\u09ac\u09a8\u09cd\u09a6\u09cd\u09ac\u09bf\u09a4\u09be  \u09aa\u09be\u09a8", instructions_visual: "\u09b6\u09ac\u09cd\u09a6 \u09a6\u09c1\u099f\u09bf \u09b2\u09bf\u0996\u09c1\u09a8:", instructions_context: "\u09ac\u09be\u0995\u09cd\u09b8\u09c7 \u09b6\u09ac\u09cd\u09a6\u0997\u09c1\u09b2\u09bf \u099f\u09be\u0987\u09aa \u0995\u09b0\u09c1\u09a8:", instructions_audio: "\u0986\u09aa\u09a8\u09bf \u09af\u09be \u09b6\u09c1\u09a8\u099b\u09c7\u09a8 \u09a4\u09be \u09b2\u09bf\u0996\u09c1\u09a8:",
        help_btn: "\u09b8\u09b9\u09be\u09df\u09a4\u09be", play_again: "\u0986\u09ac\u09be\u09b0 \u09b8\u09be\u0989\u09a8\u09cd\u09a1 \u09aa\u09cd\u09b2\u09c7 \u0995\u09b0\u09c1\u09a8", cant_hear_this: "MP3 \u09b0\u09c2\u09aa\u09c7 \u09b6\u09ac\u09cd\u09a6 \u09a1\u09be\u0989\u09a8\u09b2\u09cb\u09a1 \u0995\u09b0\u09c1\u09a8", incorrect_try_again: "\u09ac\u09c7\u09a0\u09bf\u0995\u09f7 \u0986\u09ac\u09be\u09b0 \u099a\u09c7\u09b7\u09cd\u099f\u09be \u0995\u09b0\u09c1\u09a8\u09f7", image_alt_text: "reCAPTCHA \u099a\u09cd\u09af\u09be\u09b2\u09c7\u099e\u09cd\u099c \u099a\u09bf\u09a4\u09cd\u09b0",
        privacy_and_terms: "\u0997\u09cb\u09aa\u09a8\u09c0\u09af\u09bc\u09a4\u09be \u0993 \u09b6\u09b0\u09cd\u09a4\u09be\u09ac\u09b2\u09c0"}, RecaptchaStr_ca = {visual_challenge: "Obt\u00e9n un repte visual", audio_challenge: "Obteniu una pista sonora", refresh_btn: "Obteniu una pista nova", instructions_visual: "Escriviu les dues paraules:", instructions_context: "Escriviu les paraules dels quadres:", instructions_audio: "Escriviu el que escolteu:", help_btn: "Ajuda", play_again: "Torna a reproduir el so", cant_hear_this: "Baixa el so com a MP3",
        incorrect_try_again: "No \u00e9s correcte. Torna-ho a provar.", image_alt_text: "Imatge del repte de reCAPTCHA", privacy_and_terms: "Privadesa i condicions"}, RecaptchaStr_cs = {visual_challenge: "Zobrazit vizu\u00e1ln\u00ed podobu v\u00fdrazu", audio_challenge: "P\u0159ehr\u00e1t zvukovou podobu v\u00fdrazu", refresh_btn: "Zobrazit nov\u00fd v\u00fdraz", instructions_visual: "Zadejte dv\u011b slova:", instructions_context: "Zadejte slova uveden\u00e1 v pol\u00edch:", instructions_audio: "Napi\u0161te, co jste sly\u0161eli:",
        help_btn: "N\u00e1pov\u011bda", play_again: "Znovu p\u0159ehr\u00e1t zvuk", cant_hear_this: "St\u00e1hnout zvuk ve form\u00e1tu MP3", incorrect_try_again: "\u0160patn\u011b. Zkuste to znovu.", image_alt_text: "Obr\u00e1zek reCAPTCHA", privacy_and_terms: "Ochrana soukrom\u00ed a smluvn\u00ed podm\u00ednky"}, RecaptchaStr_da = {visual_challenge: "Hent en visuel udfordring", audio_challenge: "Hent en lydudfordring", refresh_btn: "Hent en ny udfordring", instructions_visual: "Indtast de to ord:", instructions_context: "Indtast ordene i felterne:",
        instructions_audio: "Indtast det, du h\u00f8rer:", help_btn: "Hj\u00e6lp", play_again: "Afspil lyden igen", cant_hear_this: "Download lyd som MP3", incorrect_try_again: "Forkert. Pr\u00f8v igen.", image_alt_text: "reCAPTCHA-udfordringsbillede", privacy_and_terms: "Privatliv og vilk\u00e5r"}, RecaptchaStr_de = {visual_challenge: "Captcha abrufen", audio_challenge: "Audio-Captcha abrufen", refresh_btn: "Neues Captcha abrufen", instructions_visual: "Geben Sie die 2 W\u00f6rter ein:", instructions_context: "Worte aus den Feldern eingeben:",
        instructions_audio: "Geben Sie das Geh\u00f6rte ein:", help_btn: "Hilfe", play_again: "Wort erneut abspielen", cant_hear_this: "Wort als MP3 herunterladen", incorrect_try_again: "Falsch. Bitte versuchen Sie es erneut.", image_alt_text: "reCAPTCHA-Bild", privacy_and_terms: "Datenschutzerkl\u00e4rung & Nutzungsbedingungen"}, RecaptchaStr_el = {visual_challenge: "\u039f\u03c0\u03c4\u03b9\u03ba\u03ae \u03c0\u03c1\u03cc\u03ba\u03bb\u03b7\u03c3\u03b7", audio_challenge: "\u0397\u03c7\u03b7\u03c4\u03b9\u03ba\u03ae \u03c0\u03c1\u03cc\u03ba\u03bb\u03b7\u03c3\u03b7",
        refresh_btn: "\u039d\u03ad\u03b1 \u03c0\u03c1\u03cc\u03ba\u03bb\u03b7\u03c3\u03b7", instructions_visual: "\u03a0\u03bb\u03b7\u03ba\u03c4\u03c1\u03bf\u03bb. \u03c4\u03b9\u03c2 \u03bb\u03ad\u03be\u03b5\u03b9\u03c2:", instructions_context: "\u03a0\u03bb\u03b7\u03ba\u03c4\u03c1\u03bf\u03bb. \u03c4\u03b9\u03c2 \u03bb\u03ad\u03be\u03b5\u03b9\u03c2:", instructions_audio: "\u03a0\u03bb\u03b7\u03ba\u03c4\u03c1\u03bf\u03bb\u03bf\u03b3\u03ae\u03c3\u03c4\u03b5 \u03cc\u03c4\u03b9 \u03b1\u03ba\u03bf\u03cd\u03c4\u03b5:",
        help_btn: "\u0392\u03bf\u03ae\u03b8\u03b5\u03b9\u03b1", play_again: "\u0391\u03bd\u03b1\u03c0\u03b1\u03c1\u03b1\u03b3\u03c9\u03b3\u03ae \u03ae\u03c7\u03bf\u03c5 \u03be\u03b1\u03bd\u03ac", cant_hear_this: "\u039b\u03ae\u03c8\u03b7 \u03ae\u03c7\u03bf\u03c5 \u03c9\u03c2 \u039c\u03a13", incorrect_try_again: "\u039b\u03ac\u03b8\u03bf\u03c2. \u0394\u03bf\u03ba\u03b9\u03bc\u03ac\u03c3\u03c4\u03b5 \u03be\u03b1\u03bd\u03ac.", image_alt_text: "\u0395\u03b9\u03ba\u03cc\u03bd\u03b1 \u03c0\u03c1\u03cc\u03ba\u03bb\u03b7\u03c3\u03b7\u03c2 reCAPTCHA",
        privacy_and_terms: "\u0391\u03c0\u03cc\u03c1\u03c1\u03b7\u03c4\u03bf \u03ba\u03b1\u03b9 \u03cc\u03c1\u03bf\u03b9"}, RecaptchaStr_es = {visual_challenge: "Obtener una pista visual", audio_challenge: "Obtener una pista sonora", refresh_btn: "Obtener una pista nueva", instructions_visual: "Escribe las dos palabras:", instructions_context: "Escribe las palabras de los cuadros:", instructions_audio: "Escribe lo que oigas:", help_btn: "Ayuda", play_again: "Volver a reproducir el sonido", cant_hear_this: "Descargar el sonido en MP3",
        incorrect_try_again: "Incorrecto. Vu\u00e9lvelo a intentar.", image_alt_text: "Pista de imagen reCAPTCHA", privacy_and_terms: "Privacidad y condiciones"}, RecaptchaStr_es_419 = {visual_challenge: "Enfrentar un desaf\u00edo visual", audio_challenge: "Enfrentar un desaf\u00edo de audio", refresh_btn: "Enfrentar un nuevo desaf\u00edo", instructions_visual: "Escribe las dos palabras:", instructions_context: "Escribe las palabras de los cuadros:", instructions_audio: "Escribe lo que escuchas:", help_btn: "Ayuda", play_again: "Reproducir sonido de nuevo",
        cant_hear_this: "Descargar sonido en formato MP3", incorrect_try_again: "Incorrecto. Vuelve a intentarlo.", image_alt_text: "Imagen del desaf\u00edo de la reCAPTCHA", privacy_and_terms: "Privacidad y condiciones"}, RecaptchaStr_et = {visual_challenge: "Kuva kuvap\u00f5hine robotil\u00f5ks", audio_challenge: "Kuva helip\u00f5hine robotil\u00f5ks", refresh_btn: "Kuva uus robotil\u00f5ks", instructions_visual: "Tippige kaks s\u00f5na.", instructions_context: "Tippige kastides olevad s\u00f5nad.", instructions_audio: "Tippige, mida kuulete.",
        help_btn: "Abi", play_again: "Esita heli uuesti", cant_hear_this: "Laadi heli alla MP3-vormingus", incorrect_try_again: "Vale. Proovige uuesti.", image_alt_text: "reCAPTCHA robotil\u00f5ksu kujutis", privacy_and_terms: "Privaatsus ja tingimused"}, RecaptchaStr_eu = {visual_challenge: "Eskuratu ikusizko erronka", audio_challenge: "Eskuratu audio-erronka", refresh_btn: "Eskuratu erronka berria", instructions_visual: "Idatzi bi hitzak:", instructions_context: "Idatzi koadroetako hitzak:", instructions_audio: "Idatzi entzuten duzuna:",
        help_btn: "Laguntza", play_again: "Erreproduzitu soinua berriro", cant_hear_this: "Deskargatu soinua MP3 gisa", incorrect_try_again: "Ez da zuzena. Saiatu berriro.", image_alt_text: "reCAPTCHA erronkaren irudia", privacy_and_terms: "Pribatutasuna eta baldintzak"}, RecaptchaStr_fa = {visual_challenge: "\u062f\u0631\u06cc\u0627\u0641\u062a \u06cc\u06a9 \u0645\u0639\u0645\u0627\u06cc \u062f\u06cc\u062f\u0627\u0631\u06cc", audio_challenge: "\u062f\u0631\u06cc\u0627\u0641\u062a \u06cc\u06a9 \u0645\u0639\u0645\u0627\u06cc \u0635\u0648\u062a\u06cc",
        refresh_btn: "\u062f\u0631\u06cc\u0627\u0641\u062a \u06cc\u06a9 \u0645\u0639\u0645\u0627\u06cc \u062c\u062f\u06cc\u062f", instructions_visual: "\u0627\u06cc\u0646 \u062f\u0648 \u06a9\u0644\u0645\u0647 \u0631\u0627 \u062a\u0627\u06cc\u067e \u06a9\u0646\u06cc\u062f:", instructions_context: "\u0648\u0627\u0698\u0647\u200c\u0647\u0627\u06cc \u0645\u0648\u062c\u0648\u062f \u062f\u0631 \u06a9\u0627\u062f\u0631\u0647\u0627 \u0631\u0627 \u062a\u0627\u06cc\u067e \u06a9\u0646\u06cc\u062f:", instructions_audio: "\u0622\u0646\u0686\u0647 \u0631\u0627 \u06a9\u0647 \u0645\u06cc\u200c\u0634\u0646\u0648\u06cc\u062f \u062a\u0627\u06cc\u067e \u06a9\u0646\u06cc\u062f:",
        help_btn: "\u0631\u0627\u0647\u0646\u0645\u0627\u06cc\u06cc", play_again: "\u067e\u062e\u0634 \u0645\u062c\u062f\u062f \u0635\u062f\u0627", cant_hear_this: "\u062f\u0627\u0646\u0644\u0648\u062f \u0635\u062f\u0627 \u0628\u0647 \u0635\u0648\u0631\u062a MP3", incorrect_try_again: "\u0646\u0627\u062f\u0631\u0633\u062a. \u062f\u0648\u0628\u0627\u0631\u0647 \u0627\u0645\u062a\u062d\u0627\u0646 \u06a9\u0646\u06cc\u062f.", image_alt_text: "\u062a\u0635\u0648\u06cc\u0631 \u0686\u0627\u0644\u0634\u06cc reCAPTCHA", privacy_and_terms: "\u062d\u0631\u06cc\u0645 \u062e\u0635\u0648\u0635\u06cc \u0648 \u0634\u0631\u0627\u06cc\u0637"},
    RecaptchaStr_fi = {visual_challenge: "Kuvavahvistus", audio_challenge: "\u00c4\u00e4nivahvistus", refresh_btn: "Uusi kuva", instructions_visual: "Kirjoita n\u00e4kem\u00e4si kaksi sanaa", instructions_context: "Kirjoita n\u00e4kem\u00e4si sanat:", instructions_audio: "Kirjoita kuulemasi:", help_btn: "Ohje", play_again: "Toista \u00e4\u00e4ni uudelleen", cant_hear_this: "Lataa \u00e4\u00e4ni MP3-tiedostona", incorrect_try_again: "V\u00e4\u00e4rin. Yrit\u00e4 uudelleen.", image_alt_text: "reCAPTCHA-kuva", privacy_and_terms: "Tietosuoja ja k\u00e4ytt\u00f6ehdot"},
    RecaptchaStr_fil = {visual_challenge: "Kumuha ng pagsubok na visual", audio_challenge: "Kumuha ng pagsubok na audio", refresh_btn: "Kumuha ng bagong pagsubok", instructions_visual: "I-type ang dalawang mga salita:", instructions_context: "I-type ang mga salita sa mga kahon:", instructions_audio: "I-type ang iyong narinig", help_btn: "Tulong", play_again: "I-play muli ang tunog", cant_hear_this: "I-download ang tunog bilang MP3", incorrect_try_again: "Hindi wasto. Muling subukan.", image_alt_text: "larawang panghamon ng reCAPTCHA",
        privacy_and_terms: "Privacy at Mga Tuntunin"}, RecaptchaStr_fr = {visual_challenge: "Test visuel", audio_challenge: "Test audio", refresh_btn: "Nouveau test", instructions_visual: "Saisissez les deux mots :", instructions_context: "Saisissez les mots ci-dessus :", instructions_audio: "Qu'entendez-vous ?", help_btn: "Aide", play_again: "R\u00e9\u00e9couter", cant_hear_this: "T\u00e9l\u00e9charger l'audio au format MP3", incorrect_try_again: "Incorrect. Veuillez r\u00e9essayer.", image_alt_text: "Image reCAPTCHA", privacy_and_terms: "Confidentialit\u00e9 et conditions d'utilisation"},
    RecaptchaStr_fr_ca = {visual_challenge: "Obtenir un test visuel", audio_challenge: "Obtenir un test audio", refresh_btn: "Obtenir un nouveau test", instructions_visual: "Tapez les deux mots\u00a0:", instructions_context: "Tapez les mots dans les bo\u00eetes de texte\u00a0:", instructions_audio: "Tapez ce que vous entendez\u00a0:", help_btn: "Aide", play_again: "Jouer le son de nouveau", cant_hear_this: "T\u00e9l\u00e9charger le son en format MP3", incorrect_try_again: "Erreur, essayez \u00e0 nouveau", image_alt_text: "Image reCAPTCHA",
        privacy_and_terms: "Confidentialit\u00e9 et conditions d'utilisation"}, RecaptchaStr_gl = {visual_challenge: "Obter unha proba visual", audio_challenge: "Obter unha proba de audio", refresh_btn: "Obter unha proba nova", instructions_visual: "Escribe as d\u00faas palabras:", instructions_context: "Escribe as palabras nas caixas:", instructions_audio: "Escribe o que escoitas:", help_btn: "Axuda", play_again: "Reproducir o son de novo", cant_hear_this: "Descargar son como MP3", incorrect_try_again: "Incorrecto. T\u00e9ntao de novo.",
        image_alt_text: "Imaxe de proba de reCAPTCHA", privacy_and_terms: "Privacidade e termos"}, RecaptchaStr_gu = {visual_challenge: "\u0a8f\u0a95 \u0aa6\u0ac3\u0ab6\u0acd\u0aaf\u0abe\u0aa4\u0acd\u0aae\u0a95 \u0aaa\u0aa1\u0a95\u0abe\u0ab0 \u0aae\u0ac7\u0ab3\u0ab5\u0acb", audio_challenge: "\u0a8f\u0a95 \u0a91\u0aa1\u0abf\u0a93 \u0aaa\u0aa1\u0a95\u0abe\u0ab0 \u0aae\u0ac7\u0ab3\u0ab5\u0acb", refresh_btn: "\u0a8f\u0a95 \u0aa8\u0ab5\u0acb \u0aaa\u0aa1\u0a95\u0abe\u0ab0 \u0aae\u0ac7\u0ab3\u0ab5\u0acb", instructions_visual: "\u0aac\u0ac7 \u0ab6\u0aac\u0acd\u0aa6 \u0ab2\u0a96\u0acb:",
        instructions_context: "\u0aac\u0ac9\u0a95\u0acd\u0ab8\u0aae\u0abe\u0a82 \u0ab6\u0aac\u0acd\u0aa6\u0acb \u0ab2\u0a96\u0acb:", instructions_audio: "\u0aa4\u0aae\u0ac7 \u0a9c\u0ac7 \u0ab8\u0abe\u0a82\u0aad\u0ab3\u0acb \u0a9b\u0acb \u0aa4\u0ac7 \u0ab2\u0a96\u0acb:", help_btn: "\u0ab8\u0ab9\u0abe\u0aaf", play_again: "\u0aa7\u0acd\u0ab5\u0aa8\u0abf \u0aab\u0ab0\u0ac0\u0aa5\u0ac0 \u0a9a\u0ab2\u0abe\u0ab5\u0acb", cant_hear_this: "MP3 \u0aa4\u0ab0\u0ac0\u0a95\u0ac7 \u0aa7\u0acd\u0ab5\u0aa8\u0abf\u0aa8\u0ac7 \u0aa1\u0abe\u0a89\u0aa8\u0ab2\u0acb\u0aa1 \u0a95\u0ab0\u0acb",
        incorrect_try_again: "\u0a96\u0acb\u0a9f\u0ac1\u0a82. \u0aab\u0ab0\u0ac0 \u0aaa\u0acd\u0ab0\u0aaf\u0abe\u0ab8 \u0a95\u0ab0\u0acb.", image_alt_text: "reCAPTCHA \u0aaa\u0aa1\u0a95\u0abe\u0ab0 \u0a9b\u0aac\u0ac0", privacy_and_terms: "\u0a97\u0acb\u0aaa\u0aa8\u0ac0\u0aaf\u0aa4\u0abe \u0a85\u0aa8\u0ac7 \u0ab6\u0ab0\u0aa4\u0acb"}, RecaptchaStr_hi = {visual_challenge: "\u0915\u094b\u0908 \u0935\u093f\u091c\u0941\u0905\u0932 \u091a\u0941\u0928\u094c\u0924\u0940 \u0932\u0947\u0902", audio_challenge: "\u0915\u094b\u0908 \u0911\u0921\u093f\u092f\u094b \u091a\u0941\u0928\u094c\u0924\u0940 \u0932\u0947\u0902",
        refresh_btn: "\u0915\u094b\u0908 \u0928\u0908 \u091a\u0941\u0928\u094c\u0924\u0940 \u0932\u0947\u0902", instructions_visual: "\u0926\u094b \u0936\u092c\u094d\u200d\u0926 \u0932\u093f\u0916\u0947\u0902:", instructions_context: "\u0936\u092c\u094d\u200d\u0926\u094b\u0902 \u0915\u094b \u092c\u0949\u0915\u094d\u200d\u0938 \u092e\u0947\u0902 \u0932\u093f\u0916\u0947\u0902:", instructions_audio: "\u091c\u094b \u0906\u092a \u0938\u0941\u0928 \u0930\u0939\u0947 \u0939\u0948\u0902 \u0909\u0938\u0947 \u0932\u093f\u0916\u0947\u0902:",
        help_btn: "\u0938\u0939\u093e\u092f\u0924\u093e", play_again: "\u0927\u094d\u200d\u0935\u0928\u093f \u092a\u0941\u0928: \u091a\u0932\u093e\u090f\u0902", cant_hear_this: "\u0927\u094d\u200d\u0935\u0928\u093f \u0915\u094b MP3 \u0915\u0947 \u0930\u0942\u092a \u092e\u0947\u0902 \u0921\u093e\u0909\u0928\u0932\u094b\u0921 \u0915\u0930\u0947\u0902", incorrect_try_again: "\u0917\u0932\u0924. \u092a\u0941\u0928: \u092a\u094d\u0930\u092f\u093e\u0938 \u0915\u0930\u0947\u0902.", image_alt_text: "reCAPTCHA \u091a\u0941\u0928\u094c\u0924\u0940 \u091a\u093f\u0924\u094d\u0930",
        privacy_and_terms: "\u0917\u094b\u092a\u0928\u0940\u092f\u0924\u093e \u0914\u0930 \u0936\u0930\u094d\u0924\u0947\u0902"}, RecaptchaStr_hr = {visual_challenge: "Dohvati vizualni upit", audio_challenge: "Dohvati zvu\u010dni upit", refresh_btn: "Dohvati novi upit", instructions_visual: "Upi\u0161ite obje rije\u010di:", instructions_context: "Upi\u0161ite rije\u010di u okvire:", instructions_audio: "Upi\u0161ite \u0161to \u010dujete:", help_btn: "Pomo\u0107", play_again: "Ponovi zvuk", cant_hear_this: "Preuzmi zvuk u MP3 formatu",
        incorrect_try_again: "Nije to\u010dno. Poku\u0161ajte ponovno.", image_alt_text: "Slikovni izazov reCAPTCHA", privacy_and_terms: "Privatnost i odredbe"}, RecaptchaStr_hu = {visual_challenge: "Vizu\u00e1lis kih\u00edv\u00e1s k\u00e9r\u00e9se", audio_challenge: "Hangkih\u00edv\u00e1s k\u00e9r\u00e9se", refresh_btn: "\u00daj kih\u00edv\u00e1s k\u00e9r\u00e9se", instructions_visual: "Adja meg a k\u00e9t sz\u00f3t:", instructions_context: "\u00cdrja be a szavakat a mez\u0151kbe:", instructions_audio: "\u00cdrja le, amit hall:",
        help_btn: "S\u00fag\u00f3", play_again: "Hang ism\u00e9telt lej\u00e1tsz\u00e1sa", cant_hear_this: "Hang let\u00f6lt\u00e9se MP3 form\u00e1tumban", incorrect_try_again: "Hib\u00e1s. Pr\u00f3b\u00e1lkozzon \u00fajra.", image_alt_text: "reCAPTCHA ellen\u0151rz\u0151 k\u00e9p", privacy_and_terms: "Adatv\u00e9delem \u00e9s Szerz\u0151d\u00e9si Felt\u00e9telek"}, RecaptchaStr_hy = {visual_challenge: "\u054d\u057f\u0561\u0576\u0561\u056c \u057f\u0565\u057d\u0578\u0572\u0561\u056f\u0561\u0576 \u056d\u0576\u0564\u056b\u0580",
        audio_challenge: "\u054d\u057f\u0561\u0576\u0561\u056c \u0571\u0561\u0575\u0576\u0561\u0575\u056b\u0576 \u056d\u0576\u0564\u056b\u0580", refresh_btn: "\u054d\u057f\u0561\u0576\u0561\u056c \u0576\u0578\u0580 \u056d\u0576\u0564\u056b\u0580", instructions_visual: "\u0544\u0578\u0582\u057f\u0584\u0561\u0563\u0580\u0565\u0584 \u0561\u0575\u057d \u0565\u0580\u056f\u0578\u0582 \u0562\u0561\u057c\u0565\u0580\u0568\u055d", instructions_context: "\u0544\u0578\u0582\u057f\u0584\u0561\u0563\u0580\u0565\u0584 \u0562\u0561\u057c\u0565\u0580\u0568 \u057f\u0578\u0582\u0583\u0565\u0580\u0578\u0582\u0574\u055d",
        instructions_audio: "\u0544\u0578\u0582\u057f\u0584\u0561\u0563\u0580\u0565\u0584 \u0561\u0575\u0576, \u056b\u0576\u0579 \u056c\u057d\u0578\u0582\u0574 \u0565\u0584\u055d", help_btn: "\u0555\u0563\u0576\u0578\u0582\u0569\u0575\u0578\u0582\u0576", play_again: "\u0546\u057e\u0561\u0563\u0561\u0580\u056f\u0565\u056c \u0571\u0561\u0575\u0576\u0568 \u056f\u0580\u056f\u056b\u0576", cant_hear_this: "\u0532\u0565\u057c\u0576\u0565\u056c \u0571\u0561\u0575\u0576\u0568 \u0578\u0580\u057a\u0565\u057d MP3", incorrect_try_again: "\u054d\u056d\u0561\u056c \u0567: \u0553\u0578\u0580\u0571\u0565\u0584 \u056f\u0580\u056f\u056b\u0576:",
        image_alt_text: "", privacy_and_terms: "\u0533\u0561\u0572\u057f\u0576\u056b\u0578\u0582\u0569\u0575\u0561\u0576 & \u057a\u0561\u0575\u0574\u0561\u0576\u0576\u0565\u0580"}, RecaptchaStr_id = {visual_challenge: "Dapatkan kata pengujian berbentuk visual", audio_challenge: "Dapatkan kata pengujian berbentuk audio", refresh_btn: "Dapatkan kata pengujian baru", instructions_visual: "Ketik dua kata ini:", instructions_context: "Ketik kata di dalam kotak:", instructions_audio: "Ketik yang Anda dengar:", help_btn: "Bantuan",
        play_again: "Putar suara sekali lagi", cant_hear_this: "Unduh suara sebagai MP3", incorrect_try_again: "Salah. Coba lagi.", image_alt_text: "Gambar tantangan reCAPTCHA", privacy_and_terms: "Privasi & Persyaratan"}, RecaptchaStr_is = {visual_challenge: "F\u00e1 a\u00f0gangspr\u00f3f sem mynd", audio_challenge: "F\u00e1 a\u00f0gangspr\u00f3f sem hlj\u00f3\u00f0skr\u00e1", refresh_btn: "F\u00e1 n\u00fdtt a\u00f0gangspr\u00f3f", instructions_visual: "Sl\u00e1\u00f0u inn \u00feessi tv\u00f6 or\u00f0:", instructions_context: "Sl\u00e1\u00f0u or\u00f0in inn \u00ed reitina:",
        instructions_audio: "Sl\u00e1\u00f0u inn \u00fea\u00f0 sem \u00fe\u00fa heyrir:", help_btn: "Hj\u00e1lp", play_again: "Spila hlj\u00f3\u00f0 aftur", cant_hear_this: "S\u00e6kja hlj\u00f3\u00f0 sem MP3", incorrect_try_again: "Rangt. Reyndu aftur.", image_alt_text: "mynd reCAPTCHA a\u00f0gangspr\u00f3fs", privacy_and_terms: "Pers\u00f3nuvernd og skilm\u00e1lar"}, RecaptchaStr_it = {visual_challenge: "Verifica visiva", audio_challenge: "Verifica audio", refresh_btn: "Nuova verifica", instructions_visual: "Digita le due parole:",
        instructions_context: "Digita le parole nelle caselle:", instructions_audio: "Digita ci\u00f2 che senti:", help_btn: "Guida", play_again: "Riproduci di nuovo audio", cant_hear_this: "Scarica audio in MP3", incorrect_try_again: "Sbagliato. Riprova.", image_alt_text: "Immagine di verifica reCAPTCHA", privacy_and_terms: "Privacy e Termini"}, RecaptchaStr_iw = {visual_challenge: "\u05e7\u05d1\u05dc \u05d0\u05ea\u05d2\u05e8 \u05d7\u05d6\u05d5\u05ea\u05d9", audio_challenge: "\u05e7\u05d1\u05dc \u05d0\u05ea\u05d2\u05e8 \u05e9\u05de\u05e2",
        refresh_btn: "\u05e7\u05d1\u05dc \u05d0\u05ea\u05d2\u05e8 \u05d7\u05d3\u05e9", instructions_visual: "\u05d4\u05e7\u05dc\u05d3 \u05d0\u05ea \u05e9\u05ea\u05d9 \u05d4\u05de\u05d9\u05dc\u05d9\u05dd:", instructions_context: "\u05d4\u05e7\u05dc\u05d3 \u05d0\u05ea \u05d4\u05de\u05d9\u05dc\u05d9\u05dd \u05d1\u05ea\u05d9\u05d1\u05d5\u05ea:", instructions_audio: "\u05d4\u05e7\u05dc\u05d3 \u05d0\u05ea \u05de\u05d4 \u05e9\u05d0\u05ea\u05d4 \u05e9\u05d5\u05de\u05e2:", help_btn: "\u05e2\u05d6\u05e8\u05d4", play_again: "\u05d4\u05e4\u05e2\u05dc \u05e9\u05d5\u05d1 \u05d0\u05ea \u05d4\u05e9\u05de\u05e2",
        cant_hear_this: "\u05d4\u05d5\u05e8\u05d3 \u05e9\u05de\u05e2 \u05db-3MP", incorrect_try_again: "\u05e9\u05d2\u05d5\u05d9. \u05e0\u05e1\u05d4 \u05e9\u05d5\u05d1.", image_alt_text: "\u05ea\u05de\u05d5\u05e0\u05ea \u05d0\u05ea\u05d2\u05e8 \u05e9\u05dc reCAPTCHA", privacy_and_terms: "\u05e4\u05e8\u05d8\u05d9\u05d5\u05ea \u05d5\u05ea\u05e0\u05d0\u05d9\u05dd"}, RecaptchaStr_ja = {visual_challenge: "\u753b\u50cf\u3067\u78ba\u8a8d\u3057\u307e\u3059", audio_challenge: "\u97f3\u58f0\u3067\u78ba\u8a8d\u3057\u307e\u3059",
        refresh_btn: "\u5225\u306e\u5358\u8a9e\u3067\u3084\u308a\u76f4\u3057\u307e\u3059", instructions_visual: "2 \u3064\u306e\u5358\u8a9e\u3092\u5165\u529b\u3057\u307e\u3059:", instructions_context: "\u30dc\u30c3\u30af\u30b9\u5185\u306e\u5358\u8a9e\u3092\u5165\u529b\u3057\u3066\u304f\u3060\u3055\u3044:", instructions_audio: "\u805e\u3053\u3048\u305f\u5358\u8a9e\u3092\u5165\u529b\u3057\u307e\u3059:", help_btn: "\u30d8\u30eb\u30d7", play_again: "\u3082\u3046\u4e00\u5ea6\u805e\u304f", cant_hear_this: "MP3 \u3067\u97f3\u58f0\u3092\u30c0\u30a6\u30f3\u30ed\u30fc\u30c9",
        incorrect_try_again: "\u6b63\u3057\u304f\u3042\u308a\u307e\u305b\u3093\u3002\u3082\u3046\u4e00\u5ea6\u3084\u308a\u76f4\u3057\u3066\u304f\u3060\u3055\u3044\u3002", image_alt_text: "reCAPTCHA \u78ba\u8a8d\u7528\u753b\u50cf", privacy_and_terms: "\u30d7\u30e9\u30a4\u30d0\u30b7\u30fc\u3068\u5229\u7528\u898f\u7d04"}, RecaptchaStr_kn = {visual_challenge: "\u0ca6\u0cc3\u0cb6\u0ccd\u0caf \u0cb8\u0cb5\u0cbe\u0cb2\u0cca\u0c82\u0ca6\u0ca8\u0ccd\u0ca8\u0cc1 \u0cb8\u0ccd\u0cb5\u0cc0\u0c95\u0cb0\u0cbf\u0cb8\u0cbf", audio_challenge: "\u0c86\u0ca1\u0cbf\u0caf\u0ccb \u0cb8\u0cb5\u0cbe\u0cb2\u0cca\u0c82\u0ca6\u0ca8\u0ccd\u0ca8\u0cc1 \u0cb8\u0ccd\u0cb5\u0cc0\u0c95\u0cb0\u0cbf\u0cb8\u0cbf",
        refresh_btn: "\u0cb9\u0cca\u0cb8 \u0cb8\u0cb5\u0cbe\u0cb2\u0cca\u0c82\u0ca6\u0ca8\u0ccd\u0ca8\u0cc1 \u0caa\u0ca1\u0cc6\u0caf\u0cbf\u0cb0\u0cbf", instructions_visual: "\u0c8e\u0cb0\u0ca1\u0cc1 \u0caa\u0ca6\u0c97\u0cb3\u0ca8\u0ccd\u0ca8\u0cc1 \u0c9f\u0cc8\u0caa\u0ccd \u0cae\u0cbe\u0ca1\u0cbf:", instructions_context: "\u0cac\u0cbe\u0c95\u0ccd\u0cb8\u0ccd\u200c\u0ca8\u0cb2\u0ccd\u0cb2\u0cbf \u0caa\u0ca6\u0c97\u0cb3\u0ca8\u0ccd\u0ca8\u0cc1 \u0c9f\u0cc8\u0caa\u0ccd\u200c \u0cae\u0cbe\u0ca1\u0cbf:", instructions_audio: "\u0ca8\u0cbf\u0cae\u0c97\u0cc6 \u0c95\u0cc7\u0cb3\u0cbf\u0cb8\u0cc1\u0cb5\u0cc1\u0ca6\u0ca8\u0ccd\u0ca8\u0cc1 \u0c9f\u0cc8\u0caa\u0ccd\u200c \u0cae\u0cbe\u0ca1\u0cbf:",
        help_btn: "\u0cb8\u0cb9\u0cbe\u0caf", play_again: "\u0ca7\u0ccd\u0cb5\u0ca8\u0cbf\u0caf\u0ca8\u0ccd\u0ca8\u0cc1 \u0cae\u0ca4\u0ccd\u0ca4\u0cc6 \u0caa\u0ccd\u0cb2\u0cc7 \u0cae\u0cbe\u0ca1\u0cbf", cant_hear_this: "\u0ca7\u0ccd\u0cb5\u0ca8\u0cbf\u0caf\u0ca8\u0ccd\u0ca8\u0cc1 MP3 \u0cb0\u0cc2\u0caa\u0ca6\u0cb2\u0ccd\u0cb2\u0cbf \u0ca1\u0ccc\u0ca8\u0ccd\u200c\u0cb2\u0ccb\u0ca1\u0ccd \u0cae\u0cbe\u0ca1\u0cbf", incorrect_try_again: "\u0ca4\u0caa\u0ccd\u0caa\u0cbe\u0c97\u0cbf\u0ca6\u0cc6. \u0cae\u0ca4\u0ccd\u0ca4\u0cca\u0cae\u0ccd\u0cae\u0cc6 \u0caa\u0ccd\u0cb0\u0caf\u0ca4\u0ccd\u0ca8\u0cbf\u0cb8\u0cbf.",
        image_alt_text: "reCAPTCHA \u0cb8\u0cb5\u0cbe\u0cb2\u0cc1 \u0c9a\u0cbf\u0ca4\u0ccd\u0cb0", privacy_and_terms: "\u0c97\u0ccc\u0caa\u0ccd\u0caf\u0ca4\u0cc6 \u0cae\u0ca4\u0ccd\u0ca4\u0cc1 \u0ca8\u0cbf\u0caf\u0cae\u0c97\u0cb3\u0cc1"}, RecaptchaStr_ko = {visual_challenge: "\uadf8\ub9bc\uc73c\ub85c \ubcf4\uc548\ubb38\uc790 \ubc1b\uae30", audio_challenge: "\uc74c\uc131\uc73c\ub85c \ubcf4\uc548\ubb38\uc790 \ubc1b\uae30", refresh_btn: "\ubcf4\uc548\ubb38\uc790 \uc0c8\ub85c \ubc1b\uae30", instructions_visual: "\ub450 \ub2e8\uc5b4 \uc785\ub825:",
        instructions_context: "\uc785\ub825\ub780\uc5d0 \ub2e8\uc5b4 \uc785\ub825:", instructions_audio: "\uc74c\uc131 \ubcf4\uc548\ubb38\uc790 \uc785\ub825:", help_btn: "\ub3c4\uc6c0\ub9d0", play_again: "\uc74c\uc131 \ub2e4\uc2dc \ub4e3\uae30", cant_hear_this: "\uc74c\uc131\uc744 MP3\ub85c \ub2e4\uc6b4\ub85c\ub4dc", incorrect_try_again: "\ud2c0\ub838\uc2b5\ub2c8\ub2e4. \ub2e4\uc2dc \uc2dc\ub3c4\ud574 \uc8fc\uc138\uc694.", image_alt_text: "reCAPTCHA \ubcf4\uc548\ubb38\uc790 \uc774\ubbf8\uc9c0", privacy_and_terms: "\uac1c\uc778\uc815\ubcf4 \ubcf4\ud638 \ubc0f \uc57d\uad00"},
    RecaptchaStr_lt = {visual_challenge: "Gauti vaizdin\u012f atpa\u017einimo test\u0105", audio_challenge: "Gauti garso atpa\u017einimo test\u0105", refresh_btn: "Gauti nauj\u0105 atpa\u017einimo test\u0105", instructions_visual: "\u012eveskite du \u017eod\u017eius:", instructions_context: "\u012eveskite \u017eod\u017eius laukeliuose:", instructions_audio: "\u012eveskite tai, k\u0105 girdite:", help_btn: "Pagalba", play_again: "Dar kart\u0105 paleisti gars\u0105", cant_hear_this: "Atsisi\u0173sti gars\u0105 kaip MP3",
        incorrect_try_again: "Neteisingai. Bandykite dar kart\u0105.", image_alt_text: "Testo \u201ereCAPTCHA\u201c vaizdas", privacy_and_terms: "Privatumas ir s\u0105lygos"}, RecaptchaStr_lv = {visual_challenge: "Sa\u0146emt vizu\u0101lu izaicin\u0101jumu", audio_challenge: "Sa\u0146emt audio izaicin\u0101jumu", refresh_btn: "Sa\u0146emt jaunu izaicin\u0101jumu", instructions_visual: "Ierakstiet divus v\u0101rdus:", instructions_context: "Ierakstiet v\u0101rdus lodzi\u0146os:", instructions_audio: "Ierakstiet dzirdamo:", help_btn: "Pal\u012bdz\u012bba",
        play_again: "V\u0113lreiz atska\u0146ot ska\u0146u", cant_hear_this: "Lejupiel\u0101d\u0113t ska\u0146u MP3\u00a0form\u0101t\u0101", incorrect_try_again: "Nepareizi. M\u0113\u0123iniet v\u0113lreiz.", image_alt_text: "reCAPTCHA izaicin\u0101juma att\u0113ls", privacy_and_terms: "Konfidencialit\u0101te un noteikumi"}, RecaptchaStr_ml = {visual_challenge: "\u0d12\u0d30\u0d41 \u0d26\u0d43\u0d36\u0d4d\u0d2f \u0d1a\u0d32\u0d1e\u0d4d\u0d1a\u0d4d \u0d28\u0d47\u0d1f\u0d41\u0d15", audio_challenge: "\u0d12\u0d30\u0d41 \u0d13\u0d21\u0d3f\u0d2f\u0d4b \u0d1a\u0d32\u0d1e\u0d4d\u0d1a\u0d4d \u0d28\u0d47\u0d1f\u0d41\u0d15",
        refresh_btn: "\u0d12\u0d30\u0d41 \u0d2a\u0d41\u0d24\u0d3f\u0d2f \u0d1a\u0d32\u0d1e\u0d4d\u0d1a\u0d4d \u0d28\u0d47\u0d1f\u0d41\u0d15", instructions_visual: "\u0d30\u0d23\u0d4d\u0d1f\u0d4d \u0d2a\u0d26\u0d19\u0d4d\u0d19\u0d7e \u0d1f\u0d48\u0d2a\u0d4d\u0d2a\u0d4d \u0d1a\u0d46\u0d2f\u0d4d\u0d2f\u0d41\u0d15:", instructions_context: "\u0d2c\u0d4b\u0d15\u0d4d\u200c\u0d38\u0d41\u0d15\u0d33\u0d3f\u0d32\u0d46 \u0d2a\u0d26\u0d19\u0d4d\u0d19\u0d7e \u0d1f\u0d48\u0d2a\u0d4d\u0d2a\u0d41\u0d1a\u0d46\u0d2f\u0d4d\u0d2f\u0d41\u0d15:",
        instructions_audio: "\u0d15\u0d47\u0d7e\u0d15\u0d4d\u0d15\u0d41\u0d28\u0d4d\u0d28\u0d24\u0d4d \u0d1f\u0d48\u0d2a\u0d4d\u0d2a\u0d4d \u0d1a\u0d46\u0d2f\u0d4d\u0d2f\u0d42:", help_btn: "\u0d38\u0d39\u0d3e\u0d2f\u0d02", play_again: "\u0d36\u0d2c\u0d4d\u200c\u0d26\u0d02 \u0d35\u0d40\u0d23\u0d4d\u0d1f\u0d41\u0d02 \u0d2a\u0d4d\u0d32\u0d47 \u0d1a\u0d46\u0d2f\u0d4d\u0d2f\u0d41\u0d15", cant_hear_this: "\u0d36\u0d2c\u0d4d\u200c\u0d26\u0d02 MP3 \u0d06\u0d2f\u0d3f \u0d21\u0d57\u0d7a\u0d32\u0d4b\u0d21\u0d4d \u0d1a\u0d46\u0d2f\u0d4d\u0d2f\u0d41\u0d15",
        incorrect_try_again: "\u0d24\u0d46\u0d31\u0d4d\u0d31\u0d3e\u0d23\u0d4d. \u0d35\u0d40\u0d23\u0d4d\u0d1f\u0d41\u0d02 \u0d36\u0d4d\u0d30\u0d2e\u0d3f\u0d15\u0d4d\u0d15\u0d41\u0d15.", image_alt_text: "reCAPTCHA \u0d1a\u0d32\u0d1e\u0d4d\u0d1a\u0d4d \u0d07\u0d2e\u0d47\u0d1c\u0d4d", privacy_and_terms: "\u0d38\u0d4d\u0d35\u0d15\u0d3e\u0d30\u0d4d\u0d2f\u0d24\u0d2f\u0d41\u0d02 \u0d28\u0d3f\u0d2c\u0d28\u0d4d\u0d27\u0d28\u0d15\u0d33\u0d41\u0d02"}, RecaptchaStr_mr = {visual_challenge: "\u0926\u0943\u0936\u094d\u200d\u092f\u092e\u093e\u0928 \u0906\u0935\u094d\u0939\u093e\u0928 \u092a\u094d\u0930\u093e\u092a\u094d\u0924 \u0915\u0930\u093e",
        audio_challenge: "\u0911\u0921\u0940\u0913 \u0906\u0935\u094d\u0939\u093e\u0928 \u092a\u094d\u0930\u093e\u092a\u094d\u0924 \u0915\u0930\u093e", refresh_btn: "\u090f\u0915 \u0928\u0935\u0940\u0928 \u0906\u0935\u094d\u0939\u093e\u0928 \u092a\u094d\u0930\u093e\u092a\u094d\u0924 \u0915\u0930\u093e", instructions_visual: "\u0926\u094b\u0928 \u0936\u092c\u094d\u0926 \u091f\u093e\u0907\u092a \u0915\u0930\u093e:", instructions_context: "\u092c\u0949\u0915\u094d\u200d\u0938\u0947\u0938\u092e\u0927\u0940\u0932 \u0936\u092c\u094d\u200d\u0926 \u091f\u093e\u0907\u092a \u0915\u0930\u093e:",
        instructions_audio: "\u0906\u092a\u0932\u094d\u092f\u093e\u0932\u093e \u091c\u0947 \u0910\u0915\u0942 \u092f\u0947\u0908\u0932 \u0924\u0947 \u091f\u093e\u0907\u092a \u0915\u0930\u093e:", help_btn: "\u092e\u0926\u0924", play_again: "\u0927\u094d\u200d\u0935\u0928\u0940 \u092a\u0941\u0928\u094d\u0939\u093e \u092a\u094d\u200d\u0932\u0947 \u0915\u0930\u093e", cant_hear_this: "MP3 \u0930\u0941\u092a\u093e\u0924 \u0927\u094d\u200d\u0935\u0928\u0940 \u0921\u093e\u0909\u0928\u0932\u094b\u0921 \u0915\u0930\u093e",
        incorrect_try_again: "\u0905\u092f\u094b\u0917\u094d\u200d\u092f. \u092a\u0941\u0928\u094d\u200d\u0939\u093e \u092a\u094d\u0930\u092f\u0924\u094d\u200d\u0928 \u0915\u0930\u093e.", image_alt_text: "reCAPTCHA \u0906\u0935\u094d\u200d\u0939\u093e\u0928 \u092a\u094d\u0930\u0924\u093f\u092e\u093e", privacy_and_terms: "\u0917\u094b\u092a\u0928\u0940\u092f\u0924\u093e \u0906\u0923\u093f \u0905\u091f\u0940"}, RecaptchaStr_ms = {visual_challenge: "Dapatkan cabaran visual", audio_challenge: "Dapatkan cabaran audio", refresh_btn: "Dapatkan cabaran baru",
        instructions_visual: "Taip kedua-dua perkataan:", instructions_context: "Taip perkataan dalam kotak:", instructions_audio: "Taip apa yang didengari:", help_btn: "Bantuan", play_again: "Mainkan bunyi sekali lagi", cant_hear_this: "Muat turun bunyi sebagai MP3", incorrect_try_again: "Tidak betul. Cuba lagi.", image_alt_text: "Imej cabaran reCAPTCHA", privacy_and_terms: "Privasi & Syarat"}, RecaptchaStr_nl = {visual_challenge: "Een visuele uitdaging proberen", audio_challenge: "Een audio-uitdaging proberen", refresh_btn: "Een nieuwe uitdaging proberen",
        instructions_visual: "Typ de twee woorden:", instructions_context: "Typ de woorden in de vakken:", instructions_audio: "Typ wat u hoort:", help_btn: "Help", play_again: "Geluid opnieuw afspelen", cant_hear_this: "Geluid downloaden als MP3", incorrect_try_again: "Onjuist. Probeer het opnieuw.", image_alt_text: "reCAPTCHA-uitdagingsafbeelding", privacy_and_terms: "Privacy en voorwaarden"}, RecaptchaStr_no = {visual_challenge: "F\u00e5 en bildeutfordring", audio_challenge: "F\u00e5 en lydutfordring", refresh_btn: "F\u00e5 en ny utfordring",
        instructions_visual: "Skriv inn de to ordene:", instructions_context: "Skriv inn ordene i feltene:", instructions_audio: "Skriv inn det du h\u00f8rer:", help_btn: "Hjelp", play_again: "Spill av lyd p\u00e5 nytt", cant_hear_this: "Last ned lyd som MP3", incorrect_try_again: "Feil. Pr\u00f8v p\u00e5 nytt.", image_alt_text: "reCAPTCHA-utfordringsbilde", privacy_and_terms: "Personvern og vilk\u00e5r"}, RecaptchaStr_pl = {visual_challenge: "Poka\u017c podpowied\u017a wizualn\u0105", audio_challenge: "Odtw\u00f3rz podpowied\u017a d\u017awi\u0119kow\u0105",
        refresh_btn: "Nowa podpowied\u017a", instructions_visual: "Wpisz oba wyrazy:", instructions_context: "Wpisz s\u0142owa wy\u015bwietlane w polach:", instructions_audio: "Wpisz us\u0142yszane s\u0142owa:", help_btn: "Pomoc", play_again: "Odtw\u00f3rz d\u017awi\u0119k ponownie", cant_hear_this: "Pobierz d\u017awi\u0119k jako plik MP3", incorrect_try_again: "Nieprawid\u0142owo. Spr\u00f3buj ponownie.", image_alt_text: "Zadanie obrazkowe reCAPTCHA", privacy_and_terms: "Prywatno\u015b\u0107 i warunki"}, RecaptchaStr_pt = {visual_challenge: "Obter um desafio visual",
        audio_challenge: "Obter um desafio de \u00e1udio", refresh_btn: "Obter um novo desafio", instructions_visual: "Digite as duas palavras:", instructions_context: "Digite as palavras das caixas:", instructions_audio: "Digite o que voc\u00ea ouve:", help_btn: "Ajuda", play_again: "Reproduzir som novamente", cant_hear_this: "Fazer download do som no formato MP3", incorrect_try_again: "Incorreto. Tente novamente.", image_alt_text: "Imagem de desafio reCAPTCHA", privacy_and_terms: "Privacidade e Termos"}, RecaptchaStr_pt_pt =
    {visual_challenge: "Obter um desafio visual", audio_challenge: "Obter um desafio de \u00e1udio", refresh_btn: "Obter um novo desafio", instructions_visual: "Escreva as duas palavras:", instructions_context: "Escreva as palavras nas caixas:", instructions_audio: "Escreva o que ouvir:", help_btn: "Ajuda", play_again: "Reproduzir som novamente", cant_hear_this: "Transferir som como MP3", incorrect_try_again: "Incorreto. Tente novamente.", image_alt_text: "Imagem de teste reCAPTCHA", privacy_and_terms: "Privacidade e Termos de Utiliza\u00e7\u00e3o"},
    RecaptchaStr_ro = {visual_challenge: "Ob\u0163ine\u0163i un cod captcha vizual", audio_challenge: "Ob\u0163ine\u0163i un cod captcha audio", refresh_btn: "Ob\u0163ine\u0163i un nou cod captcha", instructions_visual: "Introduce\u0163i cele dou\u0103 cuvinte:", instructions_context: "Introduce\u0163i cuvintele \u00een casete:", instructions_audio: "Introduce\u0163i ceea ce auzi\u0163i:", help_btn: "Ajutor", play_again: "Reda\u0163i sunetul din nou", cant_hear_this: "Desc\u0103rca\u0163i fi\u015fierul audio ca MP3", incorrect_try_again: "Incorect. \u00cencerca\u0163i din nou.",
        image_alt_text: "Imagine de verificare reCAPTCHA", privacy_and_terms: "Confiden\u0163ialitate \u015fi termeni"}, RecaptchaStr_ru = {visual_challenge: "\u0412\u0438\u0437\u0443\u0430\u043b\u044c\u043d\u0430\u044f \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0430", audio_challenge: "\u0417\u0432\u0443\u043a\u043e\u0432\u0430\u044f \u043f\u0440\u043e\u0432\u0435\u0440\u043a\u0430", refresh_btn: "\u041e\u0431\u043d\u043e\u0432\u0438\u0442\u044c", instructions_visual: "\u0412\u0432\u0435\u0434\u0438\u0442\u0435 \u0442\u043e, \u0447\u0442\u043e \u0432\u0438\u0434\u0438\u0442\u0435:",
        instructions_context: "\u0412\u0432\u0435\u0434\u0438\u0442\u0435 \u0441\u043b\u043e\u0432\u0430:", instructions_audio: "\u0412\u0432\u0435\u0434\u0438\u0442\u0435 \u0442\u043e, \u0447\u0442\u043e \u0441\u043b\u044b\u0448\u0438\u0442\u0435:", help_btn: "\u0421\u043f\u0440\u0430\u0432\u043a\u0430", play_again: "\u041f\u0440\u043e\u0441\u043b\u0443\u0448\u0430\u0442\u044c \u0435\u0449\u0435 \u0440\u0430\u0437", cant_hear_this: "\u0417\u0430\u0433\u0440\u0443\u0437\u0438\u0442\u044c MP3-\u0444\u0430\u0439\u043b",
        incorrect_try_again: "\u041d\u0435\u043f\u0440\u0430\u0432\u0438\u043b\u044c\u043d\u043e. \u041f\u043e\u0432\u0442\u043e\u0440\u0438\u0442\u0435 \u043f\u043e\u043f\u044b\u0442\u043a\u0443.", image_alt_text: "\u041f\u0440\u043e\u0432\u0435\u0440\u043a\u0430 \u043f\u043e \u0441\u043b\u043e\u0432\u0443 reCAPTCHA", privacy_and_terms: "\u041f\u0440\u0430\u0432\u0438\u043b\u0430 \u0438 \u043f\u0440\u0438\u043d\u0446\u0438\u043f\u044b"}, RecaptchaStr_sk = {visual_challenge: "Zobrazi\u0165 vizu\u00e1lnu podobu", audio_challenge: "Prehra\u0165 zvukov\u00fa podobu",
        refresh_btn: "Zobrazi\u0165 nov\u00fd v\u00fdraz", instructions_visual: "Zadajte tieto dve slov\u00e1:", instructions_context: "Zadajte slov\u00e1 v poliach:", instructions_audio: "Zadajte, \u010do po\u010dujete:", help_btn: "Pomocn\u00edk", play_again: "Znova prehra\u0165 zvuk", cant_hear_this: "Prevzia\u0165 zvuk v podobe s\u00faboru MP3", incorrect_try_again: "Nespr\u00e1vne. Sk\u00faste to znova.", image_alt_text: "Obr\u00e1zok zadania reCAPTCHA", privacy_and_terms: "Ochrana osobn\u00fdch \u00fadajov a Zmluvn\u00e9 podmienky"},
    RecaptchaStr_sl = {visual_challenge: "Vizualni preskus", audio_challenge: "Zvo\u010dni preskus", refresh_btn: "Nov preskus", instructions_visual: "Vnesite besedi:", instructions_context: "Vnesite besede v okvir\u010dkih:", instructions_audio: "Natipkajte, kaj sli\u0161ite:", help_btn: "Pomo\u010d", play_again: "Znova predvajaj zvok", cant_hear_this: "Prenesi zvok kot MP3", incorrect_try_again: "Napa\u010dno. Poskusite znova.", image_alt_text: "Slika izziva reCAPTCHA", privacy_and_terms: "Zasebnost in pogoji"}, RecaptchaStr_sr =
    {visual_challenge: "\u041f\u0440\u0438\u043c\u0438\u0442\u0435 \u0432\u0438\u0437\u0443\u0435\u043b\u043d\u0438 \u0443\u043f\u0438\u0442", audio_challenge: "\u041f\u0440\u0438\u043c\u0438\u0442\u0435 \u0430\u0443\u0434\u0438\u043e \u0443\u043f\u0438\u0442", refresh_btn: "\u041f\u0440\u0438\u043c\u0438\u0442\u0435 \u043d\u043e\u0432\u0438 \u0443\u043f\u0438\u0442", instructions_visual: "\u041e\u0442\u043a\u0443\u0446\u0430\u0458\u0442\u0435 \u0434\u0432\u0435 \u0440\u0435\u0447\u0438:", instructions_context: "\u0423\u043a\u0443\u0446\u0430\u0458\u0442\u0435 \u0440\u0435\u0447\u0438 \u0443 \u043f\u043e\u0459\u0430:",
        instructions_audio: "\u041e\u0442\u043a\u0443\u0446\u0430\u0458\u0442\u0435 \u043e\u043d\u043e \u0448\u0442\u043e \u0447\u0443\u0458\u0435\u0442\u0435:", help_btn: "\u041f\u043e\u043c\u043e\u045b", play_again: "\u041f\u043e\u043d\u043e\u0432\u043e \u043f\u0443\u0441\u0442\u0438 \u0437\u0432\u0443\u043a", cant_hear_this: "\u041f\u0440\u0435\u0443\u0437\u043c\u0438 \u0437\u0432\u0443\u043a \u043a\u0430\u043e MP3 \u0441\u043d\u0438\u043c\u0430\u043a", incorrect_try_again: "\u041d\u0435\u0442\u0430\u0447\u043d\u043e. \u041f\u043e\u043a\u0443\u0448\u0430\u0458\u0442\u0435 \u043f\u043e\u043d\u043e\u0432\u043e.",
        image_alt_text: "\u0421\u043b\u0438\u043a\u0430 reCAPTCHA \u043f\u0440\u043e\u0432\u0435\u0440\u0435", privacy_and_terms: "\u041f\u0440\u0438\u0432\u0430\u0442\u043d\u043e\u0441\u0442 \u0438 \u0443\u0441\u043b\u043e\u0432\u0438"}, RecaptchaStr_sv = {visual_challenge: "H\u00e4mta captcha i bildformat", audio_challenge: "H\u00e4mta captcha i ljudformat", refresh_btn: "H\u00e4mta ny captcha", instructions_visual: "Skriv b\u00e5da orden:", instructions_context: "Skriv orden i rutorna:", instructions_audio: "Skriv det du h\u00f6r:",
        help_btn: "Hj\u00e4lp", play_again: "Spela upp ljudet igen", cant_hear_this: "H\u00e4mta ljud som MP3", incorrect_try_again: "Fel. F\u00f6rs\u00f6k igen.", image_alt_text: "reCAPTCHA-bild", privacy_and_terms: "Sekretess och villkor"}, RecaptchaStr_sw = {visual_challenge: "Pata herufi za kusoma", audio_challenge: "Pata herufi za kusikiliza", refresh_btn: "Pata herufi mpya", instructions_visual: "Charaza maneno mawili unayoyaona:", instructions_context: "Charaza maneno katika masanduku:", instructions_audio: "Charaza unachosikia:",
        help_btn: "Usaidizi", play_again: "Cheza sauti tena", cant_hear_this: "Pakua sauti kama MP3", incorrect_try_again: "Sio sahihi. Jaribu tena.", image_alt_text: "picha ya changamoto ya reCAPTCHA", privacy_and_terms: "Faragha & Masharti"}, RecaptchaStr_ta = {visual_challenge: "\u0baa\u0bbe\u0bb0\u0bcd\u0bb5\u0bc8 \u0b9a\u0bc7\u0bb2\u0b9e\u0bcd\u0b9a\u0bc8\u0baa\u0bcd \u0baa\u0bc6\u0bb1\u0bc1\u0b95", audio_challenge: "\u0b86\u0b9f\u0bbf\u0baf\u0bcb \u0b9a\u0bc7\u0bb2\u0b9e\u0bcd\u0b9a\u0bc8\u0baa\u0bcd \u0baa\u0bc6\u0bb1\u0bc1\u0b95",
        refresh_btn: "\u0baa\u0bc1\u0ba4\u0bbf\u0baf \u0b9a\u0bc7\u0bb2\u0b9e\u0bcd\u0b9a\u0bc8\u0baa\u0bcd \u0baa\u0bc6\u0bb1\u0bc1\u0b95", instructions_visual: "\u0b9a\u0bca\u0bb1\u0bcd\u0b95\u0bb3\u0bc8 \u0b9f\u0bc8\u0baa\u0bcd \u0b9a\u0bc6\u0baf\u0bcd\u0b95:", instructions_context: "\u0baa\u0bc6\u0b9f\u0bcd\u0b9f\u0bbf\u0baf\u0bbf\u0bb2\u0bcd \u0b89\u0bb3\u0bcd\u0bb3 \u0b9a\u0bca\u0bb1\u0bcd\u0b95\u0bb3\u0bc8 \u0b89\u0bb3\u0bcd\u0bb3\u0bbf\u0b9f\u0bc1\u0b95:", instructions_audio: "\u0b95\u0bc7\u0b9f\u0bcd\u0baa\u0ba4\u0bc8 \u0b9f\u0bc8\u0baa\u0bcd \u0b9a\u0bc6\u0baf\u0bcd\u0b95:",
        help_btn: "\u0b89\u0ba4\u0bb5\u0bbf", play_again: "\u0b92\u0bb2\u0bbf\u0baf\u0bc8 \u0bae\u0bc0\u0ba3\u0bcd\u0b9f\u0bc1\u0bae\u0bcd \u0b87\u0baf\u0b95\u0bcd\u0b95\u0bc1", cant_hear_this: "\u0b92\u0bb2\u0bbf\u0baf\u0bc8 MP3 \u0b86\u0b95 \u0baa\u0ba4\u0bbf\u0bb5\u0bbf\u0bb1\u0b95\u0bcd\u0b95\u0bc1\u0b95", incorrect_try_again: "\u0ba4\u0bb5\u0bb1\u0bbe\u0ba9\u0ba4\u0bc1. \u0bae\u0bc0\u0ba3\u0bcd\u0b9f\u0bc1\u0bae\u0bcd \u0bae\u0bc1\u0baf\u0bb2\u0bb5\u0bc1\u0bae\u0bcd.", image_alt_text: "reCAPTCHA \u0b9a\u0bc7\u0bb2\u0b9e\u0bcd\u0b9a\u0bcd \u0baa\u0b9f\u0bae\u0bcd",
        privacy_and_terms: "\u0ba4\u0ba9\u0bbf\u0baf\u0bc1\u0bb0\u0bbf\u0bae\u0bc8 & \u0bb5\u0bbf\u0ba4\u0bbf\u0bae\u0bc1\u0bb1\u0bc8\u0b95\u0bb3\u0bcd"}, RecaptchaStr_te = {visual_challenge: "\u0c12\u0c15 \u0c26\u0c43\u0c36\u0c4d\u0c2f\u0c2e\u0c3e\u0c28 \u0c38\u0c35\u0c3e\u0c32\u0c41\u0c28\u0c41 \u0c38\u0c4d\u0c35\u0c40\u0c15\u0c30\u0c3f\u0c02\u0c1a\u0c02\u0c21\u0c3f", audio_challenge: "\u0c12\u0c15 \u0c06\u0c21\u0c3f\u0c2f\u0c4b \u0c38\u0c35\u0c3e\u0c32\u0c41\u0c28\u0c41 \u0c38\u0c4d\u0c35\u0c40\u0c15\u0c30\u0c3f\u0c02\u0c1a\u0c02\u0c21\u0c3f",
        refresh_btn: "\u0c15\u0c4d\u0c30\u0c4a\u0c24\u0c4d\u0c24 \u0c38\u0c35\u0c3e\u0c32\u0c41\u0c28\u0c41 \u0c38\u0c4d\u0c35\u0c40\u0c15\u0c30\u0c3f\u0c02\u0c1a\u0c02\u0c21\u0c3f", instructions_visual: "\u0c30\u0c46\u0c02\u0c21\u0c41 \u0c2a\u0c26\u0c3e\u0c32\u0c28\u0c41 \u0c1f\u0c48\u0c2a\u0c4d \u0c1a\u0c47\u0c2f\u0c02\u0c21\u0c3f:", instructions_context: "\u0c2a\u0c26\u0c3e\u0c32\u0c28\u0c41 \u0c2a\u0c46\u0c1f\u0c4d\u0c1f\u0c46\u0c32\u0c4d\u0c32\u0c4b \u0c1f\u0c48\u0c2a\u0c4d \u0c1a\u0c47\u0c2f\u0c02\u0c21\u0c3f:",
        instructions_audio: "\u0c2e\u0c40\u0c30\u0c41 \u0c35\u0c3f\u0c28\u0c4d\u0c28\u0c26\u0c3f \u0c1f\u0c48\u0c2a\u0c4d \u0c1a\u0c47\u0c2f\u0c02\u0c21\u0c3f:", help_btn: "\u0c38\u0c39\u0c3e\u0c2f\u0c02", play_again: "\u0c27\u0c4d\u0c35\u0c28\u0c3f\u0c28\u0c3f \u0c2e\u0c33\u0c4d\u0c32\u0c40 \u0c2a\u0c4d\u0c32\u0c47 \u0c1a\u0c47\u0c2f\u0c3f", cant_hear_this: "\u0c27\u0c4d\u0c35\u0c28\u0c3f\u0c28\u0c3f MP3 \u0c35\u0c32\u0c46 \u0c21\u0c4c\u0c28\u0c4d\u200c\u0c32\u0c4b\u0c21\u0c4d \u0c1a\u0c47\u0c2f\u0c3f", incorrect_try_again: "\u0c24\u0c2a\u0c4d\u0c2a\u0c41. \u0c2e\u0c33\u0c4d\u0c32\u0c40 \u0c2a\u0c4d\u0c30\u0c2f\u0c24\u0c4d\u0c28\u0c3f\u0c02\u0c1a\u0c02\u0c21\u0c3f.",
        image_alt_text: "reCAPTCHA \u0c38\u0c35\u0c3e\u0c32\u0c41 \u0c1a\u0c3f\u0c24\u0c4d\u0c30\u0c02", privacy_and_terms: "\u0c17\u0c4b\u0c2a\u0c4d\u0c2f\u0c24 & \u0c28\u0c3f\u0c2c\u0c02\u0c27\u0c28\u0c32\u0c41"}, RecaptchaStr_th = {visual_challenge: "\u0e23\u0e31\u0e1a\u0e04\u0e27\u0e32\u0e21\u0e17\u0e49\u0e32\u0e17\u0e32\u0e22\u0e14\u0e49\u0e32\u0e19\u0e20\u0e32\u0e1e", audio_challenge: "\u0e23\u0e31\u0e1a\u0e04\u0e27\u0e32\u0e21\u0e17\u0e49\u0e32\u0e17\u0e32\u0e22\u0e14\u0e49\u0e32\u0e19\u0e40\u0e2a\u0e35\u0e22\u0e07",
        refresh_btn: "\u0e23\u0e31\u0e1a\u0e04\u0e27\u0e32\u0e21\u0e17\u0e49\u0e32\u0e17\u0e32\u0e22\u0e43\u0e2b\u0e21\u0e48", instructions_visual: "\u0e1e\u0e34\u0e21\u0e1e\u0e4c\u0e04\u0e33\u0e2a\u0e2d\u0e07\u0e04\u0e33\u0e19\u0e31\u0e49\u0e19:", instructions_context: "\u0e1e\u0e34\u0e21\u0e1e\u0e4c\u0e04\u0e33\u0e19\u0e31\u0e49\u0e19\u0e43\u0e19\u0e0a\u0e48\u0e2d\u0e07\u0e19\u0e35\u0e49:", instructions_audio: "\u0e1e\u0e34\u0e21\u0e1e\u0e4c\u0e2a\u0e34\u0e48\u0e07\u0e17\u0e35\u0e48\u0e04\u0e38\u0e13\u0e44\u0e14\u0e49\u0e22\u0e34\u0e19:",
        help_btn: "\u0e04\u0e27\u0e32\u0e21\u0e0a\u0e48\u0e27\u0e22\u0e40\u0e2b\u0e25\u0e37\u0e2d", play_again: "\u0e40\u0e25\u0e48\u0e19\u0e40\u0e2a\u0e35\u0e22\u0e07\u0e2d\u0e35\u0e01\u0e04\u0e23\u0e31\u0e49\u0e07", cant_hear_this: "\u0e14\u0e32\u0e27\u0e42\u0e2b\u0e25\u0e14\u0e40\u0e2a\u0e35\u0e22\u0e07\u0e40\u0e1b\u0e47\u0e19 MP3", incorrect_try_again: "\u0e44\u0e21\u0e48\u0e16\u0e39\u0e01\u0e15\u0e49\u0e2d\u0e07 \u0e25\u0e2d\u0e07\u0e2d\u0e35\u0e01\u0e04\u0e23\u0e31\u0e49\u0e07", image_alt_text: "\u0e23\u0e2b\u0e31\u0e2a\u0e20\u0e32\u0e1e reCAPTCHA",
        privacy_and_terms: "\u0e19\u0e42\u0e22\u0e1a\u0e32\u0e22\u0e2a\u0e48\u0e27\u0e19\u0e1a\u0e38\u0e04\u0e04\u0e25\u0e41\u0e25\u0e30\u0e02\u0e49\u0e2d\u0e01\u0e33\u0e2b\u0e19\u0e14"}, RecaptchaStr_tr = {visual_challenge: "G\u00f6rsel sorgu al", audio_challenge: "Sesli sorgu al", refresh_btn: "Yeniden y\u00fckle", instructions_visual: "\u0130ki kelimeyi yaz\u0131n:", instructions_context: "Kutudaki kelimeleri yaz\u0131n:", instructions_audio: "Duydu\u011funuzu yaz\u0131n:", help_btn: "Yard\u0131m", play_again: "Sesi tekrar \u00e7al",
        cant_hear_this: "Sesi MP3 olarak indir", incorrect_try_again: "Yanl\u0131\u015f. Tekrar deneyin.", image_alt_text: "reCAPTCHA sorusu resmi", privacy_and_terms: "Gizlilik ve \u015eartlar"}, RecaptchaStr_uk = {visual_challenge: "\u041e\u0442\u0440\u0438\u043c\u0430\u0442\u0438 \u0432\u0456\u0437\u0443\u0430\u043b\u044c\u043d\u0438\u0439 \u0442\u0435\u043a\u0441\u0442", audio_challenge: "\u041e\u0442\u0440\u0438\u043c\u0430\u0442\u0438 \u0430\u0443\u0434\u0456\u043e\u0437\u0430\u043f\u0438\u0441", refresh_btn: "\u041e\u043d\u043e\u0432\u0438\u0442\u0438 \u0442\u0435\u043a\u0441\u0442",
        instructions_visual: "\u0412\u0432\u0435\u0434\u0456\u0442\u044c \u0434\u0432\u0430 \u0441\u043b\u043e\u0432\u0430:", instructions_context: "\u0412\u0432\u0435\u0434\u0456\u0442\u044c \u0441\u043b\u043e\u0432\u0430 \u0432 \u043f\u043e\u043b\u044f:", instructions_audio: "\u0412\u0432\u0435\u0434\u0456\u0442\u044c \u043f\u043e\u0447\u0443\u0442\u0435:", help_btn: "\u0414\u043e\u0432\u0456\u0434\u043a\u0430", play_again: "\u0412\u0456\u0434\u0442\u0432\u043e\u0440\u0438\u0442\u0438 \u0437\u0430\u043f\u0438\u0441 \u0449\u0435 \u0440\u0430\u0437",
        cant_hear_this: "\u0417\u0430\u0432\u0430\u043d\u0442\u0430\u0436\u0438\u0442\u0438 \u0437\u0430\u043f\u0438\u0441 \u044f\u043a MP3", incorrect_try_again: "\u041d\u0435\u043f\u0440\u0430\u0432\u0438\u043b\u044c\u043d\u043e. \u0421\u043f\u0440\u043e\u0431\u0443\u0439\u0442\u0435 \u0449\u0435 \u0440\u0430\u0437.", image_alt_text: "\u0417\u043e\u0431\u0440\u0430\u0436\u0435\u043d\u043d\u044f \u0437\u0430\u0432\u0434\u0430\u043d\u043d\u044f reCAPTCHA", privacy_and_terms: "\u041a\u043e\u043d\u0444\u0456\u0434\u0435\u043d\u0446\u0456\u0439\u043d\u0456\u0441\u0442\u044c \u0456 \u0443\u043c\u043e\u0432\u0438"},
    RecaptchaStr_ur = {visual_challenge: "\u0627\u06cc\u06a9 \u0645\u0631\u0626\u06cc \u0686\u06cc\u0644\u0646\u062c \u062d\u0627\u0635\u0644 \u06a9\u0631\u06cc\u06ba", audio_challenge: "\u0627\u06cc\u06a9 \u0622\u0688\u06cc\u0648 \u0686\u06cc\u0644\u0646\u062c \u062d\u0627\u0635\u0644 \u06a9\u0631\u06cc\u06ba", refresh_btn: "\u0627\u06cc\u06a9 \u0646\u06cc\u0627 \u0686\u06cc\u0644\u0646\u062c \u062d\u0627\u0635\u0644 \u06a9\u0631\u06cc\u06ba", instructions_visual: "\u062f\u0648 \u0627\u0644\u0641\u0627\u0638 \u0679\u0627\u0626\u067e \u06a9\u0631\u06cc\u06ba:",
        instructions_context: "\u0627\u0644\u0641\u0627\u0638 \u062e\u0627\u0646\u0648\u06ba \u0645\u06cc\u06ba \u0679\u0627\u0626\u067e \u06a9\u0631\u06cc\u06ba:", instructions_audio: "\u062c\u0648 \u0633\u0646\u0627\u0626\u06cc \u062f\u06cc\u062a\u0627 \u06c1\u06d2 \u0648\u06c1 \u0679\u0627\u0626\u067e \u06a9\u0631\u06cc\u06ba:", help_btn: "\u0645\u062f\u062f", play_again: "\u0622\u0648\u0627\u0632 \u062f\u0648\u0628\u0627\u0631\u06c1 \u0686\u0644\u0627\u0626\u06cc\u06ba", cant_hear_this: "\u0622\u0648\u0627\u0632 \u06a9\u0648 MP3 \u06a9\u06d2 \u0628\u0637\u0648\u0631 \u0688\u0627\u0624\u0646 \u0644\u0648\u0688 \u06a9\u0631\u06cc\u06ba",
        incorrect_try_again: "\u063a\u0644\u0637\u06d4 \u062f\u0648\u0628\u0627\u0631\u06c1 \u06a9\u0648\u0634\u0634 \u06a9\u0631\u06cc\u06ba\u06d4", image_alt_text: "reCAPTCHA \u0686\u06cc\u0644\u0646\u062c \u0648\u0627\u0644\u06cc \u0634\u0628\u06cc\u06c1", privacy_and_terms: "\u0631\u0627\u0632\u062f\u0627\u0631\u06cc \u0648 \u0634\u0631\u0627\u0626\u0637"}, RecaptchaStr_vi = {visual_challenge: "Nh\u1eadn th\u1eed th\u00e1ch h\u00ecnh \u1ea3nh", audio_challenge: "Nh\u1eadn th\u1eed th\u00e1ch \u00e2m thanh", refresh_btn: "Nh\u1eadn th\u1eed th\u00e1ch m\u1edbi",
        instructions_visual: "Nh\u1eadp hai t\u1eeb:", instructions_context: "Nh\u1eadp c\u00e1c t\u1eeb trong c\u00e1c \u00f4:", instructions_audio: "Nh\u1eadp n\u1ed9i dung b\u1ea1n nghe th\u1ea5y:", help_btn: "Tr\u1ee3 gi\u00fap", play_again: "Ph\u00e1t l\u1ea1i \u00e2m thanh", cant_hear_this: "T\u1ea3i \u00e2m thanh xu\u1ed1ng d\u01b0\u1edbi d\u1ea1ng MP3", incorrect_try_again: "Kh\u00f4ng ch\u00ednh x\u00e1c. H\u00e3y th\u1eed l\u1ea1i.", image_alt_text: "H\u00ecnh x\u00e1c th\u1ef1c reCAPTCHA", privacy_and_terms: "B\u1ea3o m\u1eadt v\u00e0 \u0111i\u1ec1u kho\u1ea3n"},
    RecaptchaStr_zh_cn = {visual_challenge: "\u6536\u5230\u4e00\u4e2a\u89c6\u9891\u9080\u8bf7", audio_challenge: "\u6362\u4e00\u7ec4\u97f3\u9891\u9a8c\u8bc1\u7801", refresh_btn: "\u6362\u4e00\u7ec4\u9a8c\u8bc1\u7801", instructions_visual: "\u8bf7\u952e\u5165\u8fd9\u4e24\u4e2a\u8bcd\uff1a", instructions_context: "\u952e\u5165\u6846\u4e2d\u663e\u793a\u7684\u5b57\u8bcd\uff1a", instructions_audio: "\u8bf7\u952e\u5165\u60a8\u542c\u5230\u7684\u5185\u5bb9\uff1a", help_btn: "\u5e2e\u52a9", play_again: "\u91cd\u65b0\u64ad\u653e",
        cant_hear_this: "\u4ee5 MP3 \u683c\u5f0f\u4e0b\u8f7d\u58f0\u97f3", incorrect_try_again: "\u4e0d\u6b63\u786e\uff0c\u8bf7\u91cd\u8bd5\u3002", image_alt_text: "reCAPTCHA \u9a8c\u8bc1\u56fe\u7247", privacy_and_terms: "\u9690\u79c1\u6743\u548c\u4f7f\u7528\u6761\u6b3e"}, RecaptchaStr_zh_hk = {visual_challenge: "\u56de\u7b54\u5716\u50cf\u9a57\u8b49\u554f\u984c", audio_challenge: "\u53d6\u5f97\u8a9e\u97f3\u9a57\u8b49\u554f\u984c", refresh_btn: "\u63db\u4e00\u500b\u9a57\u8b49\u554f\u984c", instructions_visual: "\u8acb\u8f38\u5165\u9019\u5169\u500b\u5b57\uff1a",
        instructions_context: "\u5728\u6846\u5167\u8f38\u5165\u5b57\u8a5e\uff1a", instructions_audio: "\u9375\u5165\u60a8\u6240\u807d\u5230\u7684\uff1a", help_btn: "\u8aaa\u660e", play_again: "\u518d\u6b21\u64ad\u653e\u8072\u97f3", cant_hear_this: "\u5c07\u8072\u97f3\u4e0b\u8f09\u70ba MP3", incorrect_try_again: "\u4e0d\u6b63\u78ba\uff0c\u518d\u8a66\u4e00\u6b21\u3002", image_alt_text: "reCAPTCHA \u9a57\u8b49\u6587\u5b57\u5716\u7247", privacy_and_terms: "\u79c1\u96b1\u6b0a\u8207\u689d\u6b3e"}, RecaptchaStr_zh_tw = {visual_challenge: "\u53d6\u5f97\u5716\u7247\u9a57\u8b49\u554f\u984c",
        audio_challenge: "\u53d6\u5f97\u8a9e\u97f3\u9a57\u8b49\u554f\u984c", refresh_btn: "\u53d6\u5f97\u65b0\u7684\u9a57\u8b49\u554f\u984c", instructions_visual: "\u8acb\u8f38\u5165\u4e0b\u5716\u4e2d\u7684\u5169\u500b\u5b57\uff1a", instructions_context: "\u8acb\u8f38\u5165\u65b9\u584a\u4e2d\u7684\u6587\u5b57\uff1a", instructions_audio: "\u8acb\u8f38\u5165\u8a9e\u97f3\u5167\u5bb9\uff1a", help_btn: "\u8aaa\u660e", play_again: "\u518d\u6b21\u64ad\u653e", cant_hear_this: "\u4ee5 MP3 \u683c\u5f0f\u4e0b\u8f09\u8072\u97f3", incorrect_try_again: "\u9a57\u8b49\u78bc\u6709\u8aa4\uff0c\u8acb\u518d\u8a66\u4e00\u6b21\u3002",
        image_alt_text: "reCAPTCHA \u9a57\u8b49\u6587\u5b57\u5716\u7247", privacy_and_terms: "\u96b1\u79c1\u6b0a\u8207\u689d\u6b3e"}, RecaptchaStr_zu = {visual_challenge: "Thola inselelo ebonakalayo", audio_challenge: "Thola inselelo yokulalelwayo", refresh_btn: "Thola inselelo entsha", instructions_visual: "Bhala lawa magama amabili:", instructions_context: "Bhala amagama asemabhokisini:", instructions_audio: "Bhala okuzwayo:", help_btn: "Usizo", play_again: "Phinda udlale okulalelwayo futhi", cant_hear_this: "Layisha umsindo njenge-MP3",
        incorrect_try_again: "Akulungile. Zama futhi.", image_alt_text: "umfanekiso oyinselelo we-reCAPTCHA", privacy_and_terms: "Okwangasese kanye nemigomo"}, RecaptchaLangMap = {en: RecaptchaStr_en, af: RecaptchaStr_af, am: RecaptchaStr_am, ar: RecaptchaStr_ar, "ar-EG": RecaptchaStr_ar, bg: RecaptchaStr_bg, bn: RecaptchaStr_bn, ca: RecaptchaStr_ca, cs: RecaptchaStr_cs, da: RecaptchaStr_da, de: RecaptchaStr_de, el: RecaptchaStr_el, "en-GB": RecaptchaStr_en, "en-US": RecaptchaStr_en, es: RecaptchaStr_es, "es-419": RecaptchaStr_es_419, "es-ES": RecaptchaStr_es,
        et: RecaptchaStr_et, eu: RecaptchaStr_eu, fa: RecaptchaStr_fa, fi: RecaptchaStr_fi, fil: RecaptchaStr_fil, fr: RecaptchaStr_fr, "fr-CA": RecaptchaStr_fr_ca, "fr-FR": RecaptchaStr_fr, gl: RecaptchaStr_gl, gu: RecaptchaStr_gu, hi: RecaptchaStr_hi, hr: RecaptchaStr_hr, hu: RecaptchaStr_hu, hy: RecaptchaStr_hy, id: RecaptchaStr_id, is: RecaptchaStr_is, it: RecaptchaStr_it, iw: RecaptchaStr_iw, ja: RecaptchaStr_ja, kn: RecaptchaStr_kn, ko: RecaptchaStr_ko, ln: RecaptchaStr_fr, lt: RecaptchaStr_lt, lv: RecaptchaStr_lv, ml: RecaptchaStr_ml, mr: RecaptchaStr_mr,
        ms: RecaptchaStr_ms, nl: RecaptchaStr_nl, no: RecaptchaStr_no, pl: RecaptchaStr_pl, pt: RecaptchaStr_pt, "pt-BR": RecaptchaStr_pt, "pt-PT": RecaptchaStr_pt_pt, ro: RecaptchaStr_ro, ru: RecaptchaStr_ru, sk: RecaptchaStr_sk, sl: RecaptchaStr_sl, sr: RecaptchaStr_sr, sv: RecaptchaStr_sv, sw: RecaptchaStr_sw, ta: RecaptchaStr_ta, te: RecaptchaStr_te, th: RecaptchaStr_th, tr: RecaptchaStr_tr, uk: RecaptchaStr_uk, ur: RecaptchaStr_ur, vi: RecaptchaStr_vi, "zh-CN": RecaptchaStr_zh_cn, "zh-HK": RecaptchaStr_zh_hk, "zh-TW": RecaptchaStr_zh_tw, zu: RecaptchaStr_zu,
        tl: RecaptchaStr_fil, he: RecaptchaStr_iw, "in": RecaptchaStr_id, mo: RecaptchaStr_ro, zh: RecaptchaStr_zh_cn};
var RecaptchaStr = RecaptchaStr_en, RecaptchaOptions, RecaptchaDefaultOptions = {tabindex: 0, theme: "red", callback: null, lang: null, custom_theme_widget: null, custom_translations: null, includeContext: !1}, Recaptcha = {widget: null, timer_id: -1, style_set: !1, theme: null, type: "image", ajax_verify_cb: null, $: function (a) {
    return"string" == typeof a ? document.getElementById(a) : a
}, attachEvent: function (a, b, c) {
    a && a.addEventListener ? a.addEventListener(b, c, !1) : a && a.attachEvent && a.attachEvent("on" + b, c)
}, create: function (a, b, c) {
    Recaptcha.destroy();
    b && (Recaptcha.widget = Recaptcha.$(b));
    Recaptcha._init_options(c);
    Recaptcha._call_challenge(a)
}, destroy: function () {
    var a = Recaptcha.$("recaptcha_challenge_field");
    a && a.parentNode.removeChild(a);
    -1 != Recaptcha.timer_id && clearInterval(Recaptcha.timer_id);
    Recaptcha.timer_id = -1;
    if (a = Recaptcha.$("recaptcha_image"))a.innerHTML = "";
    Recaptcha.widget && ("custom" != Recaptcha.theme ? Recaptcha.widget.innerHTML = "" : Recaptcha.widget.style.display = "none", Recaptcha.widget = null)
}, focus_response_field: function () {
    var a = Recaptcha.$("recaptcha_response_field");
    a.focus()
}, get_challenge: function () {
    return"undefined" == typeof RecaptchaState ? null : RecaptchaState.challenge
}, get_response: function () {
    var a = Recaptcha.$("recaptcha_response_field");
    return!a ? null : a.value
}, ajax_verify: function (a) {
    Recaptcha.ajax_verify_cb = a;
    a = Recaptcha.get_challenge() || "";
    var b = Recaptcha.get_response() || "";
    a = Recaptcha._get_api_server() + "/ajaxverify?c=" + encodeURIComponent(a) + "&response=" + encodeURIComponent(b);
    Recaptcha._add_script(a)
}, _ajax_verify_callback: function (a) {
    Recaptcha.ajax_verify_cb(a)
},
    _get_api_server: function () {
        var a = window.location.protocol, b;
        if ("undefined" != typeof _RecaptchaOverrideApiServer)b = _RecaptchaOverrideApiServer; else {
            if ("undefined" != typeof RecaptchaState && "string" == typeof RecaptchaState.server && 0 < RecaptchaState.server.length)return RecaptchaState.server.replace(/\/+$/, "");
            b = "www.google.com/recaptcha/api"
        }
        return a + "//" + b
    }, _call_challenge: function (a) {
        a = Recaptcha._get_api_server() + "/challenge?k=" + a + "&ajax=1&cachestop=" + Math.random();
        Recaptcha.getLang_() && (a += "&lang=" + Recaptcha.getLang_());
        "undefined" != typeof RecaptchaOptions.extra_challenge_params && (a += "&" + RecaptchaOptions.extra_challenge_params);
        RecaptchaOptions.includeContext && (a += "&includeContext=1");
        Recaptcha._add_script(a)
    }, _add_script: function (a) {
        var b = document.createElement("script");
        b.type = "text/javascript";
        b.src = a;
        Recaptcha._get_script_area().appendChild(b)
    }, _get_script_area: function () {
        var a = document.getElementsByTagName("head");
        return a = !a || 1 > a.length ? document.body : a[0]
    }, _hash_merge: function (a) {
        for (var b = {}, c = 0; c < a.length; c++)for (var d in a[c])b[d] =
            a[c][d];
        "context" == b.theme && (b.includeContext = !0);
        return b
    }, _init_options: function (a) {
        a = a || {};
        RecaptchaOptions = Recaptcha._hash_merge([RecaptchaDefaultOptions, a])
    }, challenge_callback: function () {
        var a = Recaptcha.widget;
        Recaptcha._reset_timer();
        RecaptchaStr = Recaptcha._hash_merge([RecaptchaStr_en, RecaptchaLangMap[Recaptcha.getLang_()] || {}, RecaptchaOptions.custom_translations || {}]);
        window.addEventListener && window.addEventListener("unload", function (a) {
            Recaptcha.destroy()
        }, !1);
        Recaptcha._is_ie() && window.attachEvent &&
        window.attachEvent("onbeforeunload", function () {
        });
        if (0 < navigator.userAgent.indexOf("KHTML")) {
            a = document.createElement("iframe");
            a.src = "about:blank";
            a.style.height = "0px";
            a.style.width = "0px";
            a.style.visibility = "hidden";
            a.style.border = "none";
            var b = document.createTextNode("This frame prevents back/forward cache problems in Safari.");
            a.appendChild(b);
            document.body.appendChild(a)
        }
        Recaptcha._finish_widget()
    }, _add_css: function (a) {
        if (-1 != navigator.appVersion.indexOf("MSIE 5"))document.write('<style type="text/css">' +
            a + "</style>"); else {
            var b = document.createElement("style");
            b.type = "text/css";
            b.styleSheet ? b.styleSheet.cssText = a : (a = document.createTextNode(a), b.appendChild(a));
            Recaptcha._get_script_area().appendChild(b)
        }
    }, _set_style: function (a) {
        Recaptcha.style_set || (Recaptcha.style_set = !0, Recaptcha._add_css(a + "\n\n.recaptcha_is_showing_audio .recaptcha_only_if_image,.recaptcha_isnot_showing_audio .recaptcha_only_if_audio,.recaptcha_had_incorrect_sol .recaptcha_only_if_no_incorrect_sol,.recaptcha_nothad_incorrect_sol .recaptcha_only_if_incorrect_sol{display:none !important}"))
    },
    _init_builtin_theme: function () {
        var a = Recaptcha.$, b = Recaptcha._get_api_server(), c = b.length - 1;
        "/" == b[c] && (b = b.substring(0, c));
        var c = RecaptchaTemplates.VertCss, d = RecaptchaTemplates.VertHtml, e = b + "/img/" + Recaptcha.theme, f = "gif", b = Recaptcha.theme;
        "clean" == b && (c = RecaptchaTemplates.CleanCss, d = RecaptchaTemplates.CleanHtml, f = "png");
        "context" == b && (d = RecaptchaTemplates.ContextHtml);
        c = c.replace(/IMGROOT/g, e);
        Recaptcha._set_style(c);
        Recaptcha.widget.innerHTML = '<div id="recaptcha_area">' + d + "</div>";
        c = Recaptcha.getLang_();
        a("recaptcha_privacy") && (null != c && "en" == c.substring(0, 2).toLowerCase() && null != RecaptchaStr.privacy_and_terms && 0 < RecaptchaStr.privacy_and_terms.length) && (c = document.createElement("a"), c.href = "http://www.google.com/intl/en/policies/", c.target = "_blank", c.innerHTML = RecaptchaStr.privacy_and_terms, a("recaptcha_privacy").appendChild(c));
        c = function (b, c, d, h) {
            var g = a(b);
            g.src = e + "/" + c + "." + f;
            c = RecaptchaStr[d];
            g.alt = c;
            b = a(b + "_btn");
            b.title = c;
            Recaptcha.attachEvent(b, "click", h)
        };
        c("recaptcha_reload", "refresh", "refresh_btn",
            Recaptcha.reload);
        c("recaptcha_switch_audio", "audio", "audio_challenge", function () {
            Recaptcha.switch_type("audio")
        });
        c("recaptcha_switch_img", "text", "visual_challenge", function () {
            Recaptcha.switch_type("image")
        });
        c("recaptcha_whatsthis", "help", "help_btn", Recaptcha.showhelp);
        "clean" == b && (a("recaptcha_logo").src = e + "/logo." + f);
        a("recaptcha_table").className = "recaptchatable recaptcha_theme_" + Recaptcha.theme;
        b = function (b, c) {
            var d = a(b);
            d && (RecaptchaState.rtl && "span" == d.tagName.toLowerCase() && (d.dir = "rtl"),
                d.appendChild(document.createTextNode(RecaptchaStr[c])))
        };
        b("recaptcha_instructions_image", "instructions_visual");
        b("recaptcha_instructions_context", "instructions_context");
        b("recaptcha_instructions_audio", "instructions_audio");
        b("recaptcha_instructions_error", "incorrect_try_again");
        !a("recaptcha_instructions_image") && !a("recaptcha_instructions_audio") && (b = "audio" == Recaptcha.type ? RecaptchaStr.instructions_audio : RecaptchaStr.instructions_visual, b = b.replace(/:$/, ""), a("recaptcha_response_field").setAttribute("placeholder",
            b))
    }, _finish_widget: function () {
        var a = Recaptcha.$, b = RecaptchaOptions, c = b.theme, d = {blackglass: 1, clean: 1, context: 1, custom: 1, red: 1, white: 1};
        c in d || (c = "red");
        Recaptcha.theme || (Recaptcha.theme = c);
        "custom" != Recaptcha.theme ? Recaptcha._init_builtin_theme() : Recaptcha._set_style("");
        c = document.createElement("span");
        c.id = "recaptcha_challenge_field_holder";
        c.style.display = "none";
        a("recaptcha_response_field").parentNode.insertBefore(c, a("recaptcha_response_field"));
        a("recaptcha_response_field").setAttribute("autocomplete",
            "off");
        a("recaptcha_image").style.width = "300px";
        a("recaptcha_image").style.height = "57px";
        Recaptcha.should_focus = !1;
        Recaptcha._set_challenge(RecaptchaState.challenge, "image");
        Recaptcha.updateTabIndexes_();
        Recaptcha.widget && (Recaptcha.widget.style.display = "");
        b.callback && b.callback()
    }, updateTabIndexes_: function () {
        var a = Recaptcha.$, b = RecaptchaOptions;
        b.tabindex && (b = b.tabindex, a("recaptcha_response_field").tabIndex = b++, "audio" == Recaptcha.type && a("recaptcha_audio_play_again") && (a("recaptcha_audio_play_again").tabIndex =
            b++, a("recaptcha_audio_download"), a("recaptcha_audio_download").tabIndex = b++), "custom" != Recaptcha.theme && (a("recaptcha_reload_btn").tabIndex = b++, a("recaptcha_switch_audio_btn").tabIndex = b++, a("recaptcha_switch_img_btn").tabIndex = b++, a("recaptcha_whatsthis_btn").tabIndex = b))
    }, switch_type: function (a) {
        Recaptcha.type = a;
        Recaptcha.reload("audio" == Recaptcha.type ? "a" : "v");
        if ("custom" != Recaptcha.theme) {
            a = Recaptcha.$;
            var b = "audio" == Recaptcha.type ? RecaptchaStr.instructions_audio : RecaptchaStr.instructions_visual,
                b = b.replace(/:$/, "");
            a("recaptcha_response_field").setAttribute("placeholder", b)
        }
    }, reload: function (a) {
        var b = RecaptchaOptions, c = RecaptchaState;
        "undefined" == typeof a && (a = "r");
        c = Recaptcha._get_api_server() + "/reload?c=" + c.challenge + "&k=" + c.site + "&reason=" + a + "&type=" + Recaptcha.type;
        b.includeContext && (c += "&includeContext=1");
        Recaptcha.getLang_() && (c += "&lang=" + Recaptcha.getLang_());
        "undefined" != typeof b.extra_challenge_params && (c += "&" + b.extra_challenge_params);
        "audio" == Recaptcha.type && (c = b.audio_beta_12_08 ?
            c + "&audio_beta_12_08=1" : c + "&new_audio_default=1");
        Recaptcha.should_focus = "t" != a;
        Recaptcha._add_script(c)
    }, finish_reload: function (a, b) {
        RecaptchaState.is_incorrect = !1;
        Recaptcha._set_challenge(a, b);
        Recaptcha.updateTabIndexes_()
    }, _set_challenge: function (a, b) {
        var c = Recaptcha.$, d = RecaptchaState;
        d.challenge = a;
        Recaptcha.type = b;
        c("recaptcha_challenge_field_holder").innerHTML = '<input type="hidden" name="recaptcha_challenge_field" id="recaptcha_challenge_field" value="' + d.challenge + '"/>';
        if ("audio" == b)c("recaptcha_image").innerHTML =
            Recaptcha.getAudioCaptchaHtml(), Recaptcha._loop_playback(); else if ("image" == b) {
            var e = Recaptcha._get_api_server() + "/image?c=" + d.challenge;
            c("recaptcha_image").innerHTML = '<img style="display:block;" alt="' + RecaptchaStr.image_alt_text + '" height="57" width="300" src="' + e + '" />'
        }
        Recaptcha._css_toggle("recaptcha_had_incorrect_sol", "recaptcha_nothad_incorrect_sol", d.is_incorrect);
        Recaptcha._css_toggle("recaptcha_is_showing_audio", "recaptcha_isnot_showing_audio", "audio" == b);
        Recaptcha._clear_input();
        Recaptcha.should_focus &&
        Recaptcha.focus_response_field();
        Recaptcha._reset_timer()
    }, _reset_timer: function () {
        clearInterval(Recaptcha.timer_id);
        var a = Math.max(1E3 * (RecaptchaState.timeout - 60), 6E4);
        Recaptcha.timer_id = setInterval(function () {
            Recaptcha.reload("t")
        }, a);
        return a
    }, showhelp: function () {
        window.open(Recaptcha._get_help_link(), "recaptcha_popup", "width=460,height=580,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes,resizable=yes")
    }, _clear_input: function () {
        Recaptcha.$("recaptcha_response_field").value = ""
    }, _displayerror: function (a) {
        var b =
            Recaptcha.$;
        b("recaptcha_image").innerHTML = "";
        b("recaptcha_image").appendChild(document.createTextNode(a))
    }, reloaderror: function (a) {
        Recaptcha._displayerror(a)
    }, _is_ie: function () {
        return 0 < navigator.userAgent.indexOf("MSIE") && !window.opera
    }, _css_toggle: function (a, b, c) {
        var d = Recaptcha.widget;
        d || (d = document.body);
        var e = d.className, e = e.replace(RegExp("(^|\\s+)" + a + "(\\s+|$)"), " "), e = e.replace(RegExp("(^|\\s+)" + b + "(\\s+|$)"), " "), e = e + (" " + (c ? a : b));
        d.className = e
    }, _get_help_link: function () {
        var a = Recaptcha._get_api_server().replace(/\/[a-zA-Z0-9]+\/?$/,
            "/help"), a = a + ("?c=" + RecaptchaState.challenge);
        Recaptcha.getLang_() && (a += "&hl=" + Recaptcha.getLang_());
        return a
    }, playAgain: function () {
        Recaptcha.$("recaptcha_image").innerHTML = Recaptcha.getAudioCaptchaHtml();
        Recaptcha._loop_playback()
    }, _loop_playback: function () {
        var a = Recaptcha.$("recaptcha_audio_play_again");
        a && Recaptcha.attachEvent(a, "click", function () {
            Recaptcha.playAgain();
            return!1
        })
    }, getAudioCaptchaHtml: function () {
        var a = Recaptcha._get_api_server() + "/audio.mp3?c=" + RecaptchaState.challenge;
        0 == a.indexOf("https://") &&
        (a = "http://" + a.substring(8));
        var b = Recaptcha._get_api_server() + "/img/audiocaptcha.swf?v2", b = Recaptcha._is_ie() ? '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="audiocaptcha" width="0" height="0" codebase="https://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab"><param name="movie" value="' + b + '" /><param name="quality" value="high" /><param name="bgcolor" value="#869ca7" /><param name="allowScriptAccess" value="always" /></object><br/>' : '<embed src="' + b + '" quality="high" bgcolor="#869ca7" width="0" height="0" name="audiocaptcha" align="middle" play="true" loop="false" quality="high" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer" /></embed>',
            c = "";
        Recaptcha.checkFlashVer() && (c = "<br/>" + Recaptcha.getSpan_('<a id="recaptcha_audio_play_again" class="recaptcha_audio_cant_hear_link">' + RecaptchaStr.play_again + "</a>"));
        c += "<br/>" + Recaptcha.getSpan_('<a id="recaptcha_audio_download" class="recaptcha_audio_cant_hear_link" target="_blank" href="' + a + '">' + RecaptchaStr.cant_hear_this + "</a>");
        return b + c
    }, getSpan_: function (a) {
        return"<span" + (RecaptchaState && RecaptchaState.rtl ? ' dir="rtl"' : "") + ">" + a + "</span>"
    }, gethttpwavurl: function () {
        if ("audio" != Recaptcha.type)return"";
        var a = Recaptcha._get_api_server() + "/image?c=" + RecaptchaState.challenge;
        0 == a.indexOf("https://") && (a = "http://" + a.substring(8));
        return a
    }, checkFlashVer: function () {
        var a = -1 != navigator.appVersion.indexOf("MSIE"), b = -1 != navigator.appVersion.toLowerCase().indexOf("win"), c = -1 != navigator.userAgent.indexOf("Opera"), d = -1;
        if (null != navigator.plugins && 0 < navigator.plugins.length) {
            if (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"])a = navigator.plugins["Shockwave Flash 2.0"] ? " 2.0" : "",
                a = navigator.plugins["Shockwave Flash" + a].description, a = a.split(" "), a = a[2].split("."), d = a[0]
        } else if (a && b && !c)try {
            var e = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7"), f = e.GetVariable("$version"), d = f.split(" ")[1].split(",")[0]
        } catch (k) {
        }
        return 9 <= d
    }, getLang_: function () {
        return"undefined" != typeof RecaptchaState && RecaptchaState.lang ? RecaptchaState.lang : RecaptchaOptions.lang ? RecaptchaOptions.lang : null
    }};
var editor = (function () {

    // Editor elements
    var contentField, lastType, currentNodeList;

    // Editor Bubble elements
    var textOptions, optionsBox, boldButton, italicButton, quoteButton, urlButton, urlInput, underlineButton, subButton, supButton, ironyButton, h2Button;


    function init() {

        lastRange = 0;
        bindElements();

        // Set cursor position
        var range = document.createRange();
        var selection = window.getSelection();
        //range.setStart(headerField, 1);
        selection.removeAllRanges();
        selection.addRange(range);

        createEventBindings();
    }

    function createEventBindings(on) {
        document.onkeyup = checkTextHighlighting;


        // Mouse bindings
        document.onmousedown = checkTextHighlighting;
        document.onmouseup = function (event) {

            setTimeout(function () {
                checkTextHighlighting(event);
            }, 1);
        };

        // Window bindings
        window.addEventListener('resize', function (event) {
            updateBubblePosition();
        });

        // Scroll bindings. We limit the events, to free the ui
        // thread and prevent stuttering. See:
        // http://ejohn.org/blog/learning-from-twitter
        var scrollEnabled = true;
        document.body.addEventListener('scroll', function () {
            if (!scrollEnabled) {
                return;
            }

            scrollEnabled = true;

            updateBubblePosition();

            return setTimeout((function () {
                scrollEnabled = true;
            }), 250);
        });
    }

    function bindElements() {

        contentField = document.querySelector('.b-article_text');
        textOptions = document.querySelector('.text-options');

        optionsBox = textOptions.querySelector('.options');

        boldButton = textOptions.querySelector('.bold');
        boldButton.onclick = onBoldClick;

        italicButton = textOptions.querySelector('.italic');
        italicButton.onclick = onItalicClick;

        quoteButton = textOptions.querySelector('.quote');
        quoteButton.onclick = onQuoteClick;

        urlButton = textOptions.querySelector('.url');
        urlButton.onmousedown = onUrlClick;

        urlInput = textOptions.querySelector('.url-input');
        urlInput.onblur = onUrlInputBlur;
        urlInput.onkeydown = onUrlInputKeyDown;

        h2Button = textOptions.querySelector('.subtitle_h2');
        h2Button.onclick = onH2Click;

        //underlineButton = textOptions.querySelector( '.underline' );
        //underlineButton.onclick = onUnderlineClick;

        //subButton = textOptions.querySelector( '.sub' );
        //subButton.onclick = onSubClick;

        //supButton = textOptions.querySelector( '.sup' );
        //supButton.onclick = onSupClick;

        //ironyButton = textOptions.querySelector( '.irony' );
        //ironyButton.onclick = onIronyClick;
    }

    function checkTextHighlighting(event) {

        var selection = window.getSelection();

        if ((event.target.className === "url-input" ||
            event.target.classList.contains("url") ||
            (event.target.parentNode &&
                event.target.parentNode.classList.contains("ui-inputs")))) {

            currentNodeList = findNodes(selection.focusNode);
            updateBubbleStates();
            return;
        }

        // Check selections exist
        if (selection.isCollapsed === true && lastType === false) {

            onSelectorBlur();
        }

        // Text is selected
        if (selection.isCollapsed === false) {

            currentNodeList = findNodes(selection.focusNode);
            var nodeListWithClasses = findNodesWithClasses(selection.focusNode);


            // Find if highlighting is in the editable area
            if (hasNode(nodeListWithClasses, "DIV.b-article_text")) {
                var selectionParentNode = selection.focusNode.className != 'b-article_text' ? selection.focusNode.parentNode : selection.focusNode;
                var parentNodeType = '';

                if (selectionParentNode) {
                    while (selectionParentNode.className != 'b-article_text') {
                        if (!selectionParentNode.parentNode) {
                            break;
                        } else {
                            selectionParentNode = selectionParentNode.parentNode;
                        }
                    }
                    parentNodeType = selectionParentNode.getAttribute('data-type') ? ' ' + selectionParentNode.getAttribute('data-type') : '';
                }
                textOptions.className = "text-options " + parentNodeType;
                updateBubbleStates();
                updateBubblePosition();

                // Show the ui bubble
                textOptions.className = textOptions.className + ' active';
            }
        }

        lastType = selection.isCollapsed;
    }

    function updateBubblePosition() {
        var selection = window.getSelection();
        var range = selection.getRangeAt(0);
        var boundary = range.getBoundingClientRect();

        textOptions.style.top = boundary.top - 5 + window.pageYOffset + "px";
        textOptions.style.left = (boundary.left + boundary.right) / 2 + "px";
    }

    function updateBubbleStates() {

        // It would be possible to use classList here, but I feel that the
        // browser support isn't quite there, and this functionality doesn't
        // warrent a shim.
        if (hasNode(currentNodeList, 'B')) {
            boldButton.className = "bold active";
        } else {
            boldButton.className = "bold";
        }

        if (hasNode(currentNodeList, 'I')) {
            italicButton.className = "italic active";
        } else {
            italicButton.className = "italic";
        }

        if (hasNode(currentNodeList, 'BLOCKQUOTE')) {
            quoteButton.className = "quote active";
        } else {
            quoteButton.className = "quote";
        }

        if (hasNode(currentNodeList, 'A')) {
            urlButton.className = "url active";
        } else {
            urlButton.className = "url";
        }

        if (hasNode(currentNodeList, 'H2')) {
            h2Button.className = "subtitle_h2 active";
        } else {
            h2Button.className = "subtitle_h2";
        }

        /*if ( hasNode( currentNodeList, 'U') ) {
         underlineButton.className = "underline active";
         } else {
         underlineButton.className = "underline";
         }

         if ( hasNode( currentNodeList, 'SUP') ) {
         supButton.className = "sup active";
         } else {
         supButton.className = "sup";
         }

         if ( hasNode( currentNodeList, 'SUB') ) {
         subButton.className = "sub active";
         } else {
         subButton.className = "sub";
         }*/
    }

    function onSelectorBlur() {
        textOptions.className = textOptions.className + ' fade';
        setTimeout(function () {

            if (textOptions.className.indexOf('fade') !== -1) {
                textOptions.className = "text-options";
                textOptions.style.top = '-999px';
                textOptions.style.left = '-999px';
            }
        }, 260)
    }

    function findNodes(element) {

        var nodeNames = {};

        while (element.parentNode) {

            nodeNames[element.nodeName] = true;
            element = element.parentNode;

            if (element.nodeName === 'A') {
                nodeNames.url = element.href;
            }
        }

        return nodeNames;
    }

    function findNodesWithClasses(element) {

        var nodeNames = {};

        while (element.parentNode) {

            nodeNames[element.nodeName + '.' + element.className] = true;
            element = element.parentNode;

            if (element.nodeName === 'A') {
                nodeNames.url = element.href;
            }
        }

        return nodeNames;
    }

    function removeWrap(el, className) {
        if (el.parentNode && el.parentNode.className == className) {
            var newChild = document.createTextNode(el.parentNode.innerHTML);
            el.parentNode.parentNode.replaceChild(newChild, el.parentNode);
            return true;
        }
        return false;
    }

    function hasNode(nodeList, name) {

        return !!nodeList[ name ];
    }

    function onBoldClick() {
        document.execCommand('bold', false);
    }

    function onUnderlineClick() {
        document.execCommand('underline', false);
    }

    function onSubClick() {
        document.execCommand('subscript', false);
    }

    function onSupClick() {
        document.execCommand('superscript', false);
    }

    function onIronyClick() {
        var selection = window.getSelection();
        if (!removeWrap(selection.focusNode, "irony")) {
            wrapWithTag('<span class="irony">', '</span>')
        }
    }

    function onH2Click() {
        var nodeNames = findNodes(window.getSelection().focusNode);

        if (hasNode(nodeNames, 'H2')) {
            document.execCommand('formatBlock', false, 'p');
            document.execCommand('outdent');
        } else {
            document.execCommand('formatBlock', false, 'h2');
        }
    }

    function wrapWithTag(startTag, endTag) {
        var selection = getSelectionHtml(),
            windowSelection = window.getSelection(),
            range;

        if (document.selection && document.selection.createRange) { // IE
            range = document.selection.createRange();
            range.pasteHTML(startTag + selection + endTag);
        } else { // other browsers
            document.execCommand("insertHTML", false, startTag + selection + endTag);
        }
        return false;
    }

    function onItalicClick() {
        document.execCommand('italic', false);
    }

    function onQuoteClick() {

        var nodeNames = findNodes(window.getSelection().focusNode);

        if (hasNode(nodeNames, 'BLOCKQUOTE')) {
            document.execCommand('formatBlock', false, 'p');
            document.execCommand('outdent');
        } else {
            document.execCommand('formatBlock', false, 'blockquote');
        }
    }

    function onUrlClick() {

        if (optionsBox.className == 'options') {

            optionsBox.className = 'options url-mode';

            // Set timeout here to debounce the focus action
            setTimeout(function () {

                var nodeNames = findNodes(window.getSelection().focusNode);

                if (hasNode(nodeNames, "A")) {
                    urlInput.value = nodeNames.url;
                } else {
                    // Symbolize text turning into a link, which is temporary, and will never be seen.
                    document.execCommand('createLink', false, '/');
                }

                // Since typing in the input box kills the highlighted text we need
                // to save this selection, to add the url link if it is provided.
                lastSelection = window.getSelection().getRangeAt(0);
                lastType = false;

                urlInput.focus();

            }, 100);

        } else {

            optionsBox.className = 'options';
        }
    }

    function onUrlInputKeyDown(event) {

        if (event.keyCode === 13) {
            event.preventDefault();
            applyURL(urlInput.value);
            urlInput.blur();
        }
    }

    function onUrlInputBlur(event) {

        optionsBox.className = 'options';
        applyURL(urlInput.value);
        urlInput.value = '';

        currentNodeList = findNodes(window.getSelection().focusNode);
        updateBubbleStates();
    }

    function applyURL(url) {

        rehighlightLastSelection();

        // Unlink any current links
        document.execCommand('unlink', false);

        rehighlightLastSelection();

        if (url !== "") {

            // Insert HTTP if it doesn't exist.
            if (!url.match("^(http|https)://")) {

                url = "http://" + url;
            }

            document.execCommand('createLink', false, url);
        }
    }

    function rehighlightLastSelection() {

        window.getSelection().addRange(lastSelection);
    }

    function getWordCount() {

        var text = get_text(contentField);

        if (text === "") {
            return 0
        } else {
            return text.split(/\s+/).length;
        }
    }


    function getSelectionHtml() {
        var html = "";
        if (typeof window.getSelection != "undefined") {
            var sel = window.getSelection();
            if (sel.rangeCount) {
                var container = document.createElement("div");
                for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                    container.appendChild(sel.getRangeAt(i).cloneContents());
                }
                html = container.innerHTML;
            }
        } else if (typeof document.selection != "undefined") {
            if (document.selection.type == "Text") {
                html = document.selection.createRange().htmlText;
            }
        }
        return html;
    }

    return {
        init: init,
        bindElements: bindElements,
        getWordCount: getWordCount
    }

})();
