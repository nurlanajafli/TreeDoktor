/**
 * @example of component file:
 */
/*
"use strict";

exports.scripts = [
    //js files
];

exports.App = {
    init: function (app) {
        this.app = app;
        // ...
        this.bindEvents();
        // ...
    },
    bindEvents: function () {
        let self = this;
        $(this.app).find("bla-bla").initialize(function () {
            $(this).on('click', self.someMethod.bind(self)); // .bind(this)|.bind(self) желательно, чтобы передать родительский объект в метод замыкание!
        });
    }
    //...
};
 */

/**
 * @author DieZ (Sergey Kornienko) <diez.eel@gmail.com>
 * @copyright Sergey Kornienko 2020
 * @version 0.1 beta
 */
jQuery(function ($) {
    'use strict';

    // /**
    //  * Не обязательный список, можно указывать diez-app и diez-src для элемента типа section
    //  * @example <section diez-app="EquipmentGroupsApp" diez-src="equipments/components/eq-groups.js">
    //  * @type {Object.<string, string>}
    //  */
    // let appList = {
    //     'EquipmentGroupsApp': 'equipments/components/eq-groups.js'
    // };

    let config = {
        scriptCache: false
    };

    /**
     * Основной класс приложения
     * @namespace
     */
    let D = {
        /**
         * Объект используется для загруженных компонентов приложения
         */
        _components: {},

        /**
         * Объект используется для списка загруженных компонентов приложения
         */
        _loadedComponents: new Set(),

        /**
         * Объект используется для списка инициированных компонентов приложения
         */
        _initializedComponents: new Set(),

        /**
         * Объект используется для списка загруженных скриптов
         */
        _loadedScripts: new Set(),

        /**
         * Объект используется для списка модулей приложения
         */
        _modules: {},
        _loadedModules: {},

        /**
         * Предзагрузка js скриптов нужных для работы приложения.
         * в компонентах есть свой объект для предзагрузки.
         */
        scripts: [
            'assets/vendors/diez/js/includes/url-polyfill.min.js',
            'assets/vendors/diez/js/includes/promise.min.js',
            'assets/vendors/diez/js/includes/require.min.js',
            'assets/vendors/diez/js/includes/handlebars.min.js',
            'assets/vendors/diez/js/includes/jquery.initialize.min.js',
            'assets/vendors/diez/js/includes/es6-proxy-polyfill.js',
            'assets/vendors/diez/js/includes/pluralize.js',
            'assets/vendors/diez/js/includes/date.format.js',
            'assets/vendors/notebook/js/datepicker/bootstrap-datepicker.js',
            'assets/vendors/diez/js/includes/hideshow.min.js',
            /** @see https://github.com/lingtalfi/js-extension-ling */
            'assets/vendors/diez/js/includes/js-extension-ling.js'
        ],


        modules: [
            'handlebar-helpers'
        ],
        moduleComponents: {},


        /**
         * Основной метод, инициализация приложения
         */
        init: function () {
            this.handlebarsHelpers();
            Tarp.require({paths: ["/assets/js/modules/"], expose: true});
            let self = this;
            this.helper.ajaxLoader();
            let apps = [];
            $('[diez-app]').initialize(function () {
                self.loadApp(this);
                console.log('app');
            });
            // $('[d-on]').initialize(function () {
            //     $(this).on('any!',function (event){
            //         console.log(event);
            //     })
            // });
        },
        /**
         * Хелперы для шаблонизатора handlebars
         */
        handlebarsHelpers: function () {
            let self = this;
            Handlebars.registerHelper({
                //eq: (v1, v2) => v1 === v2,
                //notEq: (v1, v2) => v1 !== v2,
                ne: (v1, v2) => v1 !== v2,
                lt: (v1, v2) => v1 < v2,
                gt: (v1, v2) => v1 > v2,
                lte: (v1, v2) => v1 <= v2,
                gte: (v1, v2) => v1 >= v2,
                and() {
                    return Array.prototype.every.call(arguments, Boolean);
                },
                or() {
                    return Array.prototype.slice.call(arguments, 0, -1).some(Boolean);
                },
                empty: (v1) => (v1 === "" || v1 === 0 || v1 === "0" || v1 === null || v1 === false || (typeof v1 === 'object' && v1.length === 0)),
                notEmpty: (v1) => ((v1 !== "" && v1 !== 0 && v1 !== "0" && v1 !== null && v1 !== false) || (v1 !== null && typeof v1 === 'object' && v1.length !== 0)),
            });
            Handlebars.registerHelper('eq', function (a, b, options) {
                return a === b ? options.fn(this) : options.inverse(this);
            });
            Handlebars.registerHelper('notEq', function (a, b, options) {
                return a === b ? options.inverse(this) : options.fn(this);
            });
            Handlebars.registerHelper('instanceof', function (a, b, options) {
                return a instanceof b ? options.fn(this) : options.inverse(this);
            });
            Handlebars.registerHelper('typeof', function (a, b, options) {
                return typeof a === b ? options.fn(this) : options.inverse(this);
            });

            Handlebars.registerHelper('cast', function (a, b, options) {
                if (b === "string")
                    return String(a);
                if (b === "int")
                    return parseInt(a);
            });

            Handlebars.registerHelper('urlSegment', function (segment, def, uri) {
                return self.helper.url.segment(segment, def, uri);
            });

            Handlebars.registerHelper('math', function (a, oper, b) {
                a = a === "" ? null : a;
                b = b === "" ? null : b;
                return eval(a + oper + b);
            });

            Handlebars.registerHelper('toUrlPage', function (page) {
                return self.helper.url.setPage(page, null);
            });

            Handlebars.registerHelper('hexToRgb', function (hex) {
                return self.helper.hexToRgb(hex).join(',');
            });

            Handlebars.registerHelper('plural', function (word, count) {
                return pluralize(word, count);
            });

            Handlebars.registerHelper('contrastColor', function (hex) {
                return self.helper.contrastColor(hex);
            });

            Handlebars.registerHelper('var', function (v, k) {
                if (typeof window[v] === 'undefined')
                    return "";
                if (typeof k !== "object") {
                    if (typeof window[v][k] === 'undefined')
                        return "";
                    return window[v][k];
                }
                return window[v];
            });
            Handlebars.registerHelper('setVar', function (v, k, options) {
                options.data.root[v] = k;
            });
            Handlebars.registerHelper('round', function (v, k) {
                return self.helper.math.round(v, k);
            });

            Handlebars.registerHelper('get_currency', function () {
                return Common.get_currency();
            });

            Handlebars.registerHelper('money', function (amount) {
                return Common.money(amount);
            });
        },
        /**
         * Метод прелоадинга js скриптов из scripts
         * @param {array.<*>} sources
         * @return {Promise<void>}
         */
        loadScripts: async function (sources) {
            let self = this;
            var promises = [];
            if (Array.isArray(sources)) {
                for (const src of sources.filter(uniqueFilter)) {
                    if (!self._loadedScripts.has(src)) {
                        promises.push(self._promiseScript((typeof baseUrl === "undefined" ? '/' : baseUrl) + src));
                    }
                }
            }
            await Promise.all(promises).then(results => {
                for (const src of results) {
                    self._loadedScripts.add(src);
                }
            }, rejected => {
                console.log(rejected);
            });
        },
        /**
         * Метод прелоадинга модулей
         * @param {array.<*>} sources
         * @param {String} app
         * @return {Promise<void>}
         */
        loadModules: async function (sources, app) {
            let self = this;
            var promises = [];
            var modules = [];
            let scripts = [];
            if (Array.isArray(sources)) {
                for (const src of sources.filter(uniqueFilter)) {
                    //let name = src.toCamelCase() + 'Module';
                    if (!self._loadedModules.hasOwnProperty(src)) {
                        promises.push(self._promiseModule(src));
                        //var module = require(src);
                    } else {
                        modules[src] = self._loadedModules[src];
                    }
                }
            }
            await Promise.all(promises).then(results => {
                for (const module of results) {
                    let name = module.hasOwnProperty('name') ? module.name + "Module" : src.toCamelCase() + 'Module';
                    module.src = name;
                    self._modules[name] = module.Module;
                    modules.push(name);
                    /** Добавляем скрипты в массив для загрузки */
                    if (Array.isArray(module.scripts)) {
                        scripts.push(...module.scripts);
                    }
                }
            }, rejected => {
                console.log(rejected);
            });
            if (scripts.length !== 0) {
                await this.loadScripts(scripts);
            }
            for (const name of modules) {
                if (typeof app !== "undefined") {
                    this._components[app]._loadedModules.push(name);
                    //this._components[app]._modules[name] = this._proxyModule(Object.assign({}, this._modules[name]));
                    // for(const prop of Object.keys(this._components[app]._modules[name])){
                    //     if(prop === "init" || prop === "refresh" || prop === "observers" || prop === "events" || prop === "bindEvents" || prop === "component"){
                    //         continue;
                    //     }
                    //     // if(!(prop in this._components[app]._fn)) {
                    //     //     this._components[app]._fn[prop] = this._components[app]._modules[name][prop]
                    //     // }
                    // }

                }
            }
        },
        loadApp: async function (el) {
            let self = this;

            let modules = new Set();

            let name = $(el).attr('diez-app');
            let src = $(el).attr('diez-src');
            if (typeof name === "undefined" || typeof src === "undefined") {
                console.error("not present required params");
                return;
            }
            if (!name.endsWith('App')) {
                name += "App";
            }
            let deferred = $(el).attr('diez-deferred') !== undefined ? true : false;
            //let config = $(el).attr('diez-config');
            if (this._loadedComponents.has(name)) {
                console.log("app " + name + " already loaded");
                self.initApp(el, name);
                return;
            }
            try {
                var component = require(src);
                if (typeof component.App === "object") {
                    //component.App._fn = {};
                    component.App._modules = {};
                    component.App._loadedModules = [];
                    this._components[name] = this._proxyApp(component.App);
                    if (!globalThis.hasOwnProperty(name)) {
                        Object.defineProperty(globalThis, name, {
                            get: function () {
                                return self._components[name]
                            }
                        });
                    }
                    self._loadedComponents.add(name);
                }

                /** Загружаем все скрипты */
                if (Array.isArray(component.scripts)) {
                    await this.loadScripts(component.scripts)
                }

                /** Загружаем все модули */
                if (Array.isArray(component.modules)) {
                    await this.loadModules(component.modules, name)
                }

                if (!deferred && !$(el).is(':hidden')) {
                    // this._components[name].init(el);
                    // this._initializedComponents.add(name);
                    // $(el).attr('diez-loaded', true);
                    self.initApp(el, name);
                } else if ($(el).is(':hidden')) {
                    $(el).hideShow(function () {
                        if ($(el).is(':visible')) {
                            self.initApp(el, name);
                        }
                    })
                }


            } catch (e) {
                console.error("app " + name + " load error!");
                console.error(e.message);
                console.error(e.stack);
                return;
            }
        },
        initApp: function (el, name) {
            if (this._initializedComponents.has(name)) {
                if (typeof this._components[name].refresh === "function") {
                    this._components[name].refresh(el);
                }
            } else {
                if (typeof this._components[name].init === "function") {
                    for (const moduleName of this._components[name]._loadedModules /*Object.keys(this._components[name]._modules)*/) {
                        this.preInitModule(moduleName, this._components[name]);
                    }
                    this._components[name].init(el);
                    this._initializedComponents.add(name);
                    $(el).attr('diez-loaded', true);
                    if ('observers' in this._components[name]) {
                        this._bindObservers(this._components[name], this._components[name].observers);
                    }
                    if ('events' in this._components[name]) {
                        this._bindEvents(this._components[name], this._components[name].events);
                    }
                    for (const moduleName of this._components[name]._loadedModules /*Object.keys(this._components[name]._modules)*/) {
                        this.initModule(moduleName, this._components[name]);
                    }
                    if (typeof this._components[name].postInit === "function") {
                        this._components[name].postInit(el);
                    }
                }
            }
        },
        // preInitModule: function (name, app) {
        //     let module = this._modules[name].constructor();
        //     for (var action in module) {
        //         if(typeof action === "function" && action !== "init") {
        //             module[action] = function (...vals) {
        //                 let fn = this['before'+ action.toUpperCaseFirstChar()].bind(this);
        //                 vals = fn(...vals);
        //                 fn = this[action].bind(this);
        //                 let ret = fn(...vals);
        //                 fn = this['after'+ action.toUpperCaseFirstChar()].bind(this);
        //                 return fn(ret, ...vals);
        //             }
        //         } else {
        //             if (this._modules[name].hasOwnProperty(action))
        //                 module[action] = this._modules[name][action];
        //         }
        //     }
        //     app._modules[name] = this._proxyModule(module);
        //     app._modules[name].component = app;
        // },
        preInitModule: function (name, app) {
            app._modules[name] = this._proxyModule(Object.assign({}, this._modules[name]));
            app._modules[name].component = app;
        },
        initModule: async function (name, app) {
            await app._modules[name].init();
            if ('observers' in app._modules[name]) {
                await this._bindObservers(app._modules[name], app._modules[name].observers);
            }
            if ('events' in app._modules[name]) {
                await this._bindEvents(app._modules[name], app._modules[name].events);
            }
        },
        _promiseModule: function (src) {
            let self = this;
            return new Promise(function (resolve, reject) {
                try {
                    var module = require(src);
                    if (typeof module.Module === "undefined")
                        throw "wrong module!";
                    module.src = src;
                    resolve(module)
                } catch (e) {
                    reject(e);
                }
            });
        },
        _promiseScript: async function (src) {
            let self = this;
            return new Promise(function (resolve, reject) {
                var onload = function () {
                    resolve(src);
                };
                if (self._loadedScripts.has(src)) {
                    reject('script ' + src + ' already loaded');
                } else {
                    var script = document.createElement('script');
                    script.onload = onload;
                    script.onerror = reject;
                    script.src = src;
                    document.body.append(script);
                }
            });
        },
        _bindEvents: function (component, events, selector) {
            for (const [key, callback] of Object.entries(events)) {
                if (callback !== null && typeof callback === 'object' && Array.isArray(callback) === false) {
                    this._bindEvents(component, callback, key);
                    continue;
                }
                var sel = selector;
                var action = key;
                if (sel === undefined) {
                    [sel, action] = key.split(":");
                }
                if ('component' in component) { // Определяем, модуль ли это
                    /** Будем искать callback в приложении,
                     * а соответственно proxy приложения будет искать callback в себе и во всех модулях
                     */
                    component = component.component;
                }
                if (typeof action !== "undefined"
                    && callback !== null
                    && ((typeof callback === "string"
                        && callback in component)
                        || typeof callback === "function")
                ) {
                    $(component.app).on(action, sel, function (event, ...args) {
                        if ($(this).closest('[diez-app]').is(component.app)) {
                            if (typeof callback == "string") {
                                let boundCallback = component[callback].bind(component, event, ...args);
                                boundCallback();
                            } else {
                                let boundCallback = callback.bind(component, event, ...args);
                                boundCallback();
                            }
                        }
                    });
                }
                action = sel = undefined;
            }
        },
        _bindObservers: function (component, observers) {
            for (const [selector, callback] of Object.entries(observers)) {
                if ('component' in component) { // Определяем, модуль ли это
                    /** Будем искать callback в приложении,
                     * а соответственно proxy приложения будет искать callback в себе и во всех модулях
                     */
                    component = component.component;
                }
                if (callback !== null
                    && ((typeof callback === "string"
                        && callback in component)
                        || typeof callback === "function")
                ) {
                    $.initialize(selector, function () {
                        if ($(this).closest('[diez-app]').is(component.app)) {
                            if (typeof callback == "string") {
                                let boundCallback = component[callback].bind(this, component);
                                boundCallback();
                            } else {
                                let boundCallback = callback.bind(this, component);
                                boundCallback();
                            }
                        }
                    }, {target: component.app});
                }
            }
        },
        _proxyModule: function (module) {
            return new Proxy(module, {
                get(target, propKey, receiver) {
                    /** Заглушка для обязательных методов */
                    if (propKey === "init" || propKey === "component" || propKey === "observers" || propKey === "events" || propKey === "bindEvents") {
                        if (target.hasOwnProperty(propKey)) {
                            if (typeof target[propKey] === 'function') {
                                return target[propKey].bind(receiver)
                            } else {
                                return target[propKey]
                            }
                        } else {
                            console.warn('Module no property ' + propKey);
                            return undefined;
                        }
                    }
                    /** сначала проверяем есть ли такой метод в корне приложения без модулей */
                    if (typeof target.component !== "undefined" && Object.keys(target.component).includes(propKey)) {
                        if (typeof target.component[propKey] === 'function') {
                            return target.component[propKey].bind(target.component)
                        } else {
                            return target.component[propKey]
                        }
                    }
                    /** смотрим у себя в модуле*/
                    if (target.hasOwnProperty(propKey)) {
                        if (typeof target[propKey] === 'function') {
                            return function () {
                                let values = Array.from(arguments);
                                let upName = propKey.toUpperCaseFirstChar();
                                if (Object.keys(target.component).includes('before' + upName) && typeof target.component['before' + upName] === 'function') {
                                    let before = target.component['before' + upName].bind(target.component);
                                    values = before(...values);
                                }

                                let fn = target[propKey].bind(receiver);
                                let ret = fn(...values);

                                if (Object.keys(target.component).includes('after' + upName) && typeof target.component['after' + upName] === 'function') {
                                    let after = target.component['after' + upName].bind(target.component);
                                    return after(ret, ...values);
                                }
                                return ret;
                            };
                        } else {
                            return target[propKey]
                        }
                    }
                    /** смотрим в других модулях */
                    if (typeof target.component !== "undefined") {
                        for (const module of Object.keys(target.component._modules)) {
                            if (Object.keys(target.component._modules[module]).includes(propKey)) {
                                return (typeof target.component._modules[module][propKey] === 'function')
                                    ? target.component._modules[module][propKey].bind(target.component._modules[module])
                                    : target.component._modules[module][propKey]
                            }
                        }
                    }
                    console.warn('Module no property ' + propKey);
                    return undefined;
                },
                has(target, key) {
                    if (target.hasOwnProperty(key))
                        return true;
                    return false;
                },
                ownKeys(target) {
                    return Object.keys(target);
                },
                apply(target, thisArg, args) {
                    console.log("call function:", target, thisArg, args);
                }
            });
        },
        _proxyApp: function (app) {
            return new Proxy(app, {
                get(target, propKey, receiver) {
                    if (target.hasOwnProperty(propKey)) {
                        return (typeof target[propKey] === 'function')
                            ? target[propKey].bind(receiver)
                            : target[propKey];
                    }
                    for (const module of Object.keys(target._modules)) {
                        if (Object.keys(target._modules[module]).includes(propKey)) {
                            return (typeof target._modules[module][propKey] === 'function')
                                ? target._modules[module][propKey].bind(target._modules[module])
                                : target._modules[module][propKey]
                        }
                    }
                    console.warn('App no property ' + propKey);
                    return undefined;
                },
                has(target, key) {
                    if (target.hasOwnProperty(key))
                        return true;
                    for (const module of Object.keys(target._modules)) {
                        if (key in target._modules[module]) {
                            return true
                        }
                    }
                    return false;
                },
                ownKeys(target) {
                    return Object.keys(target);
                }
            });
        },
        /**
         * Загрузка конфига
         * @param {null|string} val
         * @param {*|boolean} def
         * @return {*|boolean|{scriptCache: boolean}}
         */
        config: function (val = null, def = false) {
            if (val === null)
                return config;
            if (config[val] !== undefined)
                return config[val];
            return def;
        },
        /**
         * различные хелперы
         */
        helper: {
            /**
             * @param {string} uri - query URL
             * @param {obj} data - query data
             * @param {obj} extra - extra arguments
             * @param {Function} successCallback -  - callable(response, ...extra)
             * @param {string} type - POST,GET
             * @param {Function} errorCallback - callable(error,error_fields)
             */
            ajax: function (uri, data, extra, successCallback, errorCallback, type = "POST", loader = false) {
                var config = {
                    type: type,
                    beforeSend: function () {
                        if (loader)
                            $(loader).append('<div id="loading">\n' + '<img id="loading-image" src="/assets/img/ajax-loaders/32x32.gif" alt="Loading..." />\n' + '</div>');
                    },
                    url: this.url.prepare(uri),
                    data: data,
                    global: false,
                    dataType: "json"
                };
                if (data instanceof FormData) {
                    config.processData = false;
                    config.contentType = false;
                }
                $.ajax(config)
                    .done(function (response) {
                        if (response.status === "ok") {
                            if (successCallback instanceof Function) {
                                successCallback(response, ...extra);
                            }
                        } else if (errorCallback instanceof Function) {
                            errorCallback(
                                response.error !== undefined && response.error.length !== 0 ? response.error : null,
                                response.errors !== undefined && response.errors.length !== 0 ? response.errors : null,
                                ...extra
                            );
                        }
                    })
                    .fail(function (jqXHR, exception) {
                        var msg = '';
                        if (jqXHR.status === 0) {
                            msg = 'Not connect.\n Verify Network.';
                        } else if (jqXHR.status == 404) {
                            msg = 'Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            msg = '!Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            msg = '!Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            msg = '!Time out error.';
                        } else if (exception === 'abort') {
                            msg = '!Ajax request aborted.';
                        } else {
                            msg = '!Uncaught Error.\n' + jqXHR.responseText;
                        }
                        if (errorCallback instanceof Function) {
                            errorCallback(msg, null, ...extra);
                        }
                    })
                    .always(function () {
                        if (loader)
                            $(loader).find('#loading').remove();
                        $(loader).find('#loading').remove();
                    });
            },
            ajaxLoader: function () {
                var styles = `
                #loading {
                    width: 100%;
                    height: 100%;
                    top: 0;
                    left: 0;
                    position: absolute;
                    display: block;
                    opacity: 0.8;
                    background-color: #fff;
                    z-index: 10002;
                    text-align: center;
                }
            
                #loading-image {
                    position: fixed;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    z-index: 10000;
                }
                `;
                var styleSheet = document.createElement("style");
                styleSheet.type = "text/css";
                styleSheet.innerText = styles;
                document.head.appendChild(styleSheet);
                // $(document).unbind('ajaxStart');
                // $(document).unbind('ajaxStop');
                // $(document).ajaxStart(function () {
                //     $(el).append('<div id="loading">\n' + '<img id="loading-image" src="/assets/img/ajax-loaders/32x32.gif" alt="Loading..." />\n' + '</div>')
                // });
                // $(document).ajaxStop(function() {
                //     $(el).find('#loading').remove();
                // });
            },
            url: {
                _parse: function (link) {
                    var url = document.createElement('a');
                    url.href = link;
                    return url;
                },
                /**
                 * @param {number} segment Segment number
                 * @param {string|null} def Default value
                 * @param {string|null} url Url string
                 * @return {string}
                 */
                segment: function (segment, def = null, url = null) {
                    if (url === null)
                        url = window.location.href;
                    var segments = this._parse(url).pathname.split('/');
                    segments.shift();
                    return segments[segment - 1] === undefined ? def : segments[segment - 1];
                },
                setPath: function (path, url = null) {
                    if (url === null)
                        url = window.location.href;
                    var obj = this._parse(url);
                    if (Array.isArray(path))
                        path.join('/');
                    obj.pathname = path;
                    return obj.href.toString();
                },
                getParamVal: function (param, url = null) {
                    if (url === null)
                        url = window.location.href;
                    var obj = this._parse(url);
                    var segments = obj.pathname.split('/');
                    var reversed = segments.reverse();
                    for (var i = 0; i < reversed.length; i++) {
                        if (reversed[i + 1] === param) {
                            return reversed[i]
                        }
                    }
                    return undefined;
                },
                setParamVal: function (param, val, url = null) {
                    if (url === null)
                        url = window.location.href;
                    var obj = this._parse(url);
                    var segments = obj.pathname.split('/');
                    var reversed = segments.reverse();
                    for (var i = 0; i < reversed.length; i++) {
                        if (reversed[i + 1] === param) {
                            reversed[i] = val;
                            segments = reversed.reverse();
                            obj.pathname = segments.join('/');
                            return obj.href.toString();
                        }
                    }
                    return false;
                },
                getPage: function (url = null) {
                    let page = 1;
                    if (url === null)
                        url = window.location.href;
                    var obj = this._parse(url);
                    var segments = obj.pathname.split('/');
                    var reversed = segments.reverse();
                    if (reversed[1] === "page") {
                        page = reversed[0];
                    }
                    return page;
                },
                setPage: function (page, url = null) {
                    if (url === null)
                        url = window.location.href;
                    var obj = this._parse(url);
                    var segments = obj.pathname.split('/');
                    var reversed = segments.reverse();
                    if (reversed[1] === "page") {
                        reversed[0] = page;
                    } else {
                        if (reversed[0] === "")
                            reversed.shift();
                        reversed.unshift('page');
                        reversed.unshift(page);
                    }
                    segments = reversed.reverse();
                    obj.pathname = segments.join('/');
                    return obj.href.toString();
                },
                setQuery: function (query = {}, url = null) {
                    if (url === null)
                        url = window.location.href;
                    let obj = this._parse(url);
                    let qs = obj.search.substring(1);
                    let oldQuery = jsx.queryStringToObject(qs);
                    for (const i in query) {
                        if (typeof query[i] === "object") {
                            if (Array.isArray(query[i]) && query[i].length === 0) {
                                if (i in oldQuery) delete oldQuery[i];
                            } else if (Object.keys(query[i]).length === 0) {
                                if (i in oldQuery) delete oldQuery[i];
                            } else {
                                oldQuery[i] = query[i];
                            }
                        } else if (typeof query[i] === "undefined" || query[i] === false || query[i] === "" || query[i] === null) {
                            if (i in oldQuery) delete oldQuery[i];
                        } else {
                            oldQuery[i] = query[i];
                        }
                    }
                    let newQuery = jsx.objectToQueryString(oldQuery);
                    if (newQuery !== "") {
                        obj.search = '?' + newQuery;
                    } else {
                        obj.search = "";
                    }
                    return obj.href.toString();
                    // //var vars = [];
                    // var oldQuery = {};
                    // if (qs !== "") {
                    //     vars = qs.split('&');
                    //     for (var i = 0; i < vars.length; i++) {
                    //         var pair = vars[i].split('=');
                    //         oldQuery[pair[0]] = pair[1];
                    //     }
                    // }
                    // for (const i in query) {
                    //     if (typeof query[i] !== "undefined" && typeof query[i] == "object") {
                    //         for (const k in query[i]) {
                    //             let key = i + '[' + k + ']';
                    //             if ((typeof query[i][k] === "undefined" || query[i] === false || query[i] === "")) {
                    //                 if (key in oldQuery) delete oldQuery[key];
                    //             } else {
                    //                 oldQuery[key] = query[i][k];
                    //             }
                    //         }
                    //     } else if ((typeof query[i] === "undefined" || query[i] === false || query[i] === "")) {
                    //         if (i in oldQuery) delete oldQuery[i];
                    //     } else {
                    //         oldQuery[i] = query[i];
                    //     }
                    // }
                    // if (oldQuery.length !== 0) {
                    //     vars = [];
                    //     for (i in oldQuery) {
                    //         vars.push(i + '=' + oldQuery[i])
                    //     }
                    //     var newQuery = "?" + vars.join('&');
                    //     obj.search = newQuery;
                    // }
                    // return obj.href.toString();
                },
                getQuery: function (variable, url) {
                    if (typeof url === "undefined")
                        url = window.location.href;
                    let obj = this._parse(url);
                    let query = obj.search.substring(1);
                    let vars = jsx.queryStringToObject(query);
                    if (typeof variable === "undefined")
                        return vars;
                    if (variable in vars) {
                        return vars[variable]
                    }
                    return undefined;
                },
                /**
                 * @param {string} string - uri string
                 * @param {object|array} query - query object
                 */
                prepare: function (string, query = []) {
                    if (query.length !== 0) {
                        if (query instanceof obj) {
                            var arr = [];
                            $.each(query, function (index, value) {
                                arr.push([index, value].join('='));
                            });
                            query = arr;
                        }
                    }
                    let url = baseUrl + string + (query.length !== 0 ? '?' + query.join('&') : '');
                    return url.replace(/([^\:])\/\//gi, '$1/');
                },
                route: function (route, args = {}) {
                    var url = route.formatUnicorn(args)
                        .replace(/\{.*?\}/gi, '');
                    return url.replace(/([^\:])\/\//gi, '$1/')
                }
            },
            hexToRgb: function (hex) {
                var rgb = hex.replace(/^#?([a-f\d]{1,2})([a-f\d]{1,2})([a-f\d]{1,2})$/i
                    , (m, r, g, b) => '#' + (r.length == 1 ? r + r : r) + (g.length == 1 ? g + g : g) + (b.length == 1 ? b + b : b))
                    .substring(1).match(/.{2}/g)
                    .map(x => parseInt(x, 16));
                if (rgb[2] === undefined)
                    rgb.push(rgb[1]);
                return rgb;
            },
            contrastColor: function (hex) {
                var rgb = this.hexToRgb(hex);
                var lum = (((0.299 * rgb[0]) + ((0.587 * rgb[1]) + (0.114 * rgb[2]))));
                return lum > 186 ? "#000000" : "#ffffff";
            },
            sys: {
                can: function (obj, method) {
                    return ((typeof obj[method]) == "function")
                },
                test: function (obj) {
                    if (typeof obj === "undefined")
                        throw "wrong component";
                    if (!this.can(obj, 'init'))
                        throw "Component should implement init method";
                },
                getMethods: (obj) => Object.getOwnPropertyNames(obj).filter(item => typeof obj[item] === 'function'),
                isIterable: (value) => Symbol.iterator in Object(value)
            },
            math: {
                round: function (num, scale) {
                    if (!("" + num).includes("e")) {
                        return +(Math.round(num + "e+" + scale) + "e-" + scale);
                    } else {
                        var arr = ("" + num).split("e");
                        var sig = "";
                        if (+arr[1] + scale > 0) {
                            sig = "+";
                        }
                        return +(Math.round(+arr[0] + "e" + sig + (+arr[1] + scale)) + "e-" + scale);
                    }
                }
            },
            file: {
                _types: {
                    image: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) ? vType.match('image.*') && !vType.match(/(tiff?|wmf)$/i) : vName.match(/\.(gif|png|jpe?g)$/i);
                    },
                    html: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) ? vType == 'text/html' : vName.match(/\.(htm|html)$/i);
                    },
                    pdf: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) ? vType == 'application/pdf' : vName.match(/\.(pdf)$/i);
                    },
                    office: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) &&
                            vType.match(/(word|excel|powerpoint|office)$/i) ||
                            vName.match(/\.(docx?|xlsx?|pptx?|pps|potx?)$/i);
                    },
                    gdocs: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) &&
                            vType.match(/(word|excel|powerpoint|office|iwork-pages|tiff?)$/i) ||
                            vName.match(/\.(rtf|docx?|xlsx?|pptx?|pps|potx?|ods|odt|pages|ai|dxf|ttf|tiff?|wmf|e?ps)$/i);
                    },
                    text: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) &&
                            vType.match('text.*') || vName.match(/\.(txt|md|csv|nfo|php|ini)$/i);
                    },
                    video: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) &&
                            vType.match(/\.video\/(ogg|mp4|webm)$/i) || vName.match(/\.(og?|mp4|webm)$/i);
                    },
                    audio: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) &&
                            vType.match(/\.audio\/(ogg|mp3|wav)$/i) || vName.match(/\.(ogg|mp3|wav)$/i);
                    },
                    flash: function (vType, vName) {
                        return (typeof vType !== "undefined" && vType !== null) &&
                            vType == 'application/x-shockwave-flash' || vName.match(/\.(swf)$/i);
                    },
                    other: function (vType, vName) {
                        return true;
                    },
                },
                type: function (vType, vName) {
                    if (typeof vName === "undefined" || vName === null)
                        return 'other';
                    for (const key of Object.keys(this._types)) {
                        if (this._types[key](vType, vName)) {
                            return key;
                        }
                    }
                    return 'other';
                }
            }
        },
        exchangeData: {},
        exchanger: {
            set: function (param, value) {
                D.exchangeData[param] = value;
            },
            get: function (param, def) {
                if (typeof def === 'undefined')
                    def = false;
                if (typeof D.exchangeData[param] !== 'undefined') {
                    let temp;
                    return (temp = D.exchangeData[param]) && delete D.exchangeData[param] && temp;
                }
                return def;
            }
        }
    };

    /**
     * Различные корневые хелперы
     */
    String.prototype.formatUnicorn = String.prototype.formatUnicorn ||
        function () {
            "use strict";
            var str = this.toString();
            if (arguments.length) {
                var t = typeof arguments[0];
                var key;
                var args = ("string" === t || "number" === t) ?
                    Array.prototype.slice.call(arguments)
                    : arguments[0];

                for (key in args) {
                    str = str.replace(new RegExp("\\{" + key + "\\}", "gi"), args[key]);
                }
            }

            return str;
        };

    String.prototype.replaceArray = String.prototype.replaceArray ||
        function (find, replace) {
            var replaceString = this;
            var regex;
            for (var i = 0; i < find.length; i++) {
                regex = new RegExp(find[i], "g");
                replaceString = replaceString.replace(regex, replace[i]);
            }
            return replaceString;
        };
    String.prototype.toCamelCase = String.prototype.toCamelCase ||
        function () {
            return this
                .replace(/[\s\-_\.\/]+([^ \-\_\.\/])/g, function ($1) {
                    return $1.toUpperCase();
                })
                .replace(/[\s\-_\.\/]/g, '')
                .replace(/^(.)/, function ($1) {
                    return $1.toLowerCase();
                });
        };

    String.prototype.camelizeOne = String.prototype.camelizeOne ||
        function () {
            return this.replace(/(?:^\w|[A-Z]|\b\w)/g, function (letter, index) {
                return index == 0 ? letter.toLowerCase() : letter.toUpperCase();
            }).replace(/\s+/g, '');
        };

    String.prototype.camelizeTwo = String.prototype.camelizeTwo ||
        function () {
            return this.replace(/(?:^\w|[A-Z]|\b\w|\s+)/g, function (match, index) {
                if (+match === 0) return ""; // or if (/\s+/.test(match)) for white spaces
                return index == 0 ? match.toLowerCase() : match.toUpperCase();
            });
        };

    String.prototype.toUpperCaseFirstChar = String.prototype.toUpperCaseFirstChar ||
        function () {
            return this.substr(0, 1).toUpperCase() + this.substr(1);
        };

    String.prototype.toLowerCaseFirstChar = String.prototype.toLowerCaseFirstChar ||
        function () {
            return this.substr(0, 1).toLowerCase() + this.substr(1);
        };

    String.prototype.toUpperCaseEachWord = String.prototype.toUpperCaseEachWord ||
        function (delim) {
            delim = delim ? delim : ' ';
            return this.split(delim).map(function (v) {
                return v.toUpperCaseFirstChar()
            }).join(delim);
        };

    String.prototype.toLowerCaseEachWord = String.prototype.toLowerCaseEachWord ||
        function (delim) {
            delim = delim ? delim : ' ';
            return this.split(delim).map(function (v) {
                return v.toLowerCaseFirstChar()
            }).join(delim);
        };

    String.prototype.toCamelCaseSimoVersion = String.prototype.toCamelCaseSimoVersion ||
        function () {
            var re = /(?:-|\s)+([^-\s])/g;
            return function (capFirst) {
                var str = (' ' + this).replace(re, function (a, b) {
                    return b.toUpperCase();
                });
                return capFirst ? str : (str.substr(0, 1).toLowerCase() + str.substr(1));
            };
        }();

    const uniqueFilter = (value, index, self) => {
        return self.indexOf(value) === index
    };

    // Object.prototype.getKeyByValue = Object.prototype.getKeyByValue ||
    //     function (value) {
    //         return Object.keys(this).find(key => this[key] === value);
    //     };

    // Array.prototype.findByKey = Array.prototype.findByKey ||
    //     function (value, key) {
    //         return (this.findIndex(obj => typeof obj === "object" && obj[key] === value) === -1) ? false : true;
    //     };

    // Function.prototype.exec = Object.prototype.exec = function () {
    //     return null
    // };
    /**
     * Define to global main App (D)
     * @name D
     * @memberOf window
     */
    Object.defineProperty(globalThis, 'D', {
        get: function () {
            return D
        }
    });
    //
    // var oldJQueryEventTrigger = $.event.trigger;
    // $.event.trigger = function (event, data, elem, onlyHandlers) {
    //     console.log( event, data, elem, onlyHandlers );
    //     oldJQueryEventTrigger( event, data, elem, onlyHandlers );
    // };

    // Override for adding event listeners
    // var oldAddEventListener = EventTarget.prototype.addEventListener;
    // EventTarget.prototype.addEventListener = function(eventName, eventHandler)
    // {
    //     oldAddEventListener.call(this, eventName, function(event) {
    //         console.log(eventName, event);
    //         eventHandler(event);
    //     });
    // };
    // var oldDispatchEvent = EventTarget.prototype.dispatchEvent;
    // EventTarget.prototype.dispatchEvent = function(event)
    // {
    //     console.log(event);
    //     oldDispatchEvent(event)
    // };
    /**
     * Инициализация приложения
     */
    (async () => {
        await D.loadScripts(D.scripts);
        D.init();
    })();
});
