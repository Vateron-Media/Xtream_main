(function ($) {
	'use strict';

	$ = $ && $.hasOwnProperty('default') ? $['default'] : $;

	/**
	 * Check if the passed argument is the
	 * the passed type.
	 *
	 * @param {string} type
	 * @param {*} arg
	 * @returns {boolean}
	 */
	function isTypeof(type, arg) {
		return typeof arg === type;
	}

	/**
	 * @type {function(*): boolean}
	 */
	var isString = isTypeof.bind(null, 'string');

	/**
	 * @type {function(*): boolean}
	 */
	var isUndefined = isTypeof.bind(null, 'undefined');

	/**
	 * @type {function(*): boolean}
	 */
	var isFunction = isTypeof.bind(null, 'function');

	/**
	 * @type {function(*): boolean}
	 */
	var isNumber = isTypeof.bind(null, 'number');


	/**
	 * Returns true if an object has no keys
	 *
	 * @param {!Object} obj
	 * @returns {boolean}
	 */
	function isEmptyObject(obj) {
		return !Object.keys(obj).length;
	}

	/**
	 * Extends the first object with any extra objects passed
	 *
	 * If the first argument is boolean and set to true
	 * it will extend child arrays and objects recursively.
	 *
	 * @param {!Object|boolean} targetArg
	 * @param {...Object} source
	 * @return {Object}
	 */
	function extend(targetArg, sourceArg) {
		var isTargetBoolean = targetArg === !!targetArg;
		var i = isTargetBoolean ? 2 : 1;
		var target = isTargetBoolean ? sourceArg : targetArg;
		var isDeep = isTargetBoolean ? targetArg : false;

		for (; i < arguments.length; i++) {
			var source = arguments[i];

			// Copy all properties for jQuery compatibility
			/* eslint guard-for-in: off */
			for (var key in source) {
				var value = source[key];

				// Skip undefined values to match jQuery and
				// skip if target to prevent infinite loop
				if (!isUndefined(value)) {
					var isObject = value !== null && typeof value === 'object' &&
						Object.getPrototypeOf(value) === Object.prototype;
					var isArray = Array.isArray(value);

					if (isDeep && (isObject || isArray)) {
						target[key] = extend(
							true,
							target[key] || (isArray ? [] : {}),
							value
						);
					} else {
						target[key] = value;
					}
				}
			}
		}

		return target;
	}

	/**
	 * Removes an item from the passed array
	 *
	 * @param {!Array} arr
	 * @param {*} item
	 */
	function arrayRemove(arr, item) {
		var i = arr.indexOf(item);

		if (i > -1) {
			arr.splice(i, 1);
		}
	}

	/**
	 * Iterates over an array or object
	 *
	 * @param {!Object|Array} obj
	 * @param {function(*, *)} fn
	 */
	function each(obj, fn) {
		if (Array.isArray(obj) || 'length' in obj && isNumber(obj.length)) {
			for (var i = 0; i < obj.length; i++) {
				fn(i, obj[i]);
			}
		} else {
			Object.keys(obj).forEach(function (key) {
				fn(key, obj[key]);
			});
		}
	}

	/**
	 * Cache of camelCase CSS property names
	 * @type {Object<string, string>}
	 */
	var cssPropertyNameCache = {};

	/**
	 * Node type constant for element nodes
	 *
	 * @type {number}
	 */
	var ELEMENT_NODE = 1;

	/**
	 * Node type constant for text nodes
	 *
	 * @type {number}
	 */
	var TEXT_NODE = 3;

	/**
	 * Node type constant for comment nodes
	 *
	 * @type {number}
	 */


	/**
	 * Node type document nodes
	 *
	 * @type {number}
	 */


	/**
	 * Node type constant for document fragments
	 *
	 * @type {number}
	 */


	function toFloat(value) {
		value = parseFloat(value);

		return isFinite(value) ? value : 0;
	}

	/**
	 * Creates an element with the specified attributes
	 *
	 * Will create it in the current document unless context
	 * is specified.
	 *
	 * @param {!string} tag
	 * @param {!Object<string, string>} [attributes]
	 * @param {!Document} [context]
	 * @returns {!HTMLElement}
	 */
	function createElement(tag, attributes, context) {
		var node = (context || document).createElement(tag);

		each(attributes || {}, function (key, value) {
			if (key === 'style') {
				node.style.cssText = value;
			} else if (key in node) {
				node[key] = value;
			} else {
				node.setAttribute(key, value);
			}
		});

		return node;
	}

	/**
	 * Returns an array of parents that matches the selector
	 *
	 * @param {!HTMLElement} node
	 * @param {!string} [selector]
	 * @returns {Array<HTMLElement>}
	 */


	/**
	 * Gets the first parent node that matches the selector
	 *
	 * @param {!HTMLElement} node
	 * @param {!string} [selector]
	 * @returns {HTMLElement|undefined}
	 */
	function parent(node, selector) {
		var parent = node || {};

		while ((parent = parent.parentNode) && !/(9|11)/.test(parent.nodeType)) {
			if (!selector || is(parent, selector)) {
				return parent;
			}
		}
	}

	/**
	 * Checks the passed node and all parents and
	 * returns the first matching node if any.
	 *
	 * @param {!HTMLElement} node
	 * @param {!string} selector
	 * @returns {HTMLElement|undefined}
	 */
	function closest(node, selector) {
		return is(node, selector) ? node : parent(node, selector);
	}

	/**
	 * Removes the node from the DOM
	 *
	 * @param {!HTMLElement} node
	 */
	function remove(node) {
		if (node.parentNode) {
			node.parentNode.removeChild(node);
		}
	}

	/**
	 * Appends child to parent node
	 *
	 * @param {!HTMLElement} node
	 * @param {!HTMLElement} child
	 */
	function appendChild(node, child) {
		node.appendChild(child);
	}

	/**
	 * Finds any child nodes that match the selector
	 *
	 * @param {!HTMLElement} node
	 * @param {!string} selector
	 * @returns {NodeList}
	 */
	function find(node, selector) {
		return node.querySelectorAll(selector);
	}

	/**
	 * For on() and off() if to add/remove the event
	 * to the capture phase
	 *
	 * @type {boolean}
	 */
	var EVENT_CAPTURE = true;

	/**
	 * For on() and off() if to add/remove the event
	 * to the bubble phase
	 *
	 * @type {boolean}
	 */


	/**
	 * Adds an event listener for the specified events.
	 *
	 * Events should be a space separated list of events.
	 *
	 * If selector is specified the handler will only be
	 * called when the event target matches the selector.
	 *
	 * @param {!Node} node
	 * @param {string} events
	 * @param {string} [selector]
	 * @param {function(Object)} fn
	 * @param {boolean} [capture=false]
	 * @see off()
	 */
	// eslint-disable-next-line max-params
	function on(node, events, selector, fn, capture) {
		events.split(' ').forEach(function (event) {
			var handler;

			if (isString(selector)) {
				handler = fn['_sce-event-' + event + selector] || function (e) {
					var target = e.target;
					while (target && target !== node) {
						if (is(target, selector)) {
							fn.call(target, e);
							return;
						}

						target = target.parentNode;
					}
				};

				fn['_sce-event-' + event + selector] = handler;
			} else {
				handler = selector;
				capture = fn;
			}

			node.addEventListener(event, handler, capture || false);
		});
	}

	/**
	 * Removes an event listener for the specified events.
	 *
	 * @param {!Node} node
	 * @param {string} events
	 * @param {string} [selector]
	 * @param {function(Object)} fn
	 * @param {boolean} [capture=false]
	 * @see on()
	 */
	// eslint-disable-next-line max-params
	function off(node, events, selector, fn, capture) {
		events.split(' ').forEach(function (event) {
			var handler;

			if (isString(selector)) {
				handler = fn['_sce-event-' + event + selector];
			} else {
				handler = selector;
				capture = fn;
			}

			node.removeEventListener(event, handler, capture || false);
		});
	}

	/**
	 * If only attr param is specified it will get
	 * the value of the attr param.
	 *
	 * If value is specified but null the attribute
	 * will be removed otherwise the attr value will
	 * be set to the passed value.
	 *
	 * @param {!HTMLElement} node
	 * @param {!string} attr
	 * @param {?string} [value]
	 */
	function attr(node, attr, value) {
		if (arguments.length < 3) {
			return node.getAttribute(attr);
		}

		// eslint-disable-next-line eqeqeq, no-eq-null
		if (value == null) {
			removeAttr(node, attr);
		} else {
			node.setAttribute(attr, value);
		}
	}

	/**
	 * Removes the specified attribute
	 *
	 * @param {!HTMLElement} node
	 * @param {!string} attr
	 */
	function removeAttr(node, attr) {
		node.removeAttribute(attr);
	}

	/**
	 * Sets the passed elements display to none
	 *
	 * @param {!HTMLElement} node
	 */
	function hide(node) {
		css(node, 'display', 'none');
	}

	/**
	 * Sets the passed elements display to default
	 *
	 * @param {!HTMLElement} node
	 */
	function show(node) {
		css(node, 'display', '');
	}

	/**
	 * Toggles an elements visibility
	 *
	 * @param {!HTMLElement} node
	 */
	function toggle(node) {
		if (isVisible(node)) {
			hide(node);
		} else {
			show(node);
		}
	}

	/**
	 * Gets a computed CSS values or sets an inline CSS value
	 *
	 * Rules should be in camelCase format and not
	 * hyphenated like CSS properties.
	 *
	 * @param {!HTMLElement} node
	 * @param {!Object|string} rule
	 * @param {string|number} [value]
	 * @return {string|number|undefined}
	 */
	function css(node, rule, value) {
		if (arguments.length < 3) {
			if (isString(rule)) {
				return node.nodeType === 1 ? getComputedStyle(node)[rule] : null;
			}

			each(rule, function (key, value) {
				css(node, key, value);
			});
		} else {
			// isNaN returns false for null, false and empty strings
			// so need to check it's truthy or 0
			var isNumeric = (value || value === 0) && !isNaN(value);
			node.style[rule] = isNumeric ? value + 'px' : value;
		}
	}


	/**
	 * Gets or sets thee data attributes on a node
	 *
	 * Unlike the jQuery version this only stores data
	 * in the DOM attributes which means only strings
	 * can be stored.
	 *
	 * @param {Node} node
	 * @param {string} [key]
	 * @param {string} [value]
	 * @return {Object|undefined}
	 */
	function data(node, key, value) {
		var argsLength = arguments.length;
		var data = {};

		if (node.nodeType === ELEMENT_NODE) {
			if (argsLength === 1) {
				each(node.attributes, function (_, attr) {
					if (/^data\-/i.test(attr.name)) {
						data[attr.name.substr(5)] = attr.value;
					}
				});

				return data;
			}

			if (argsLength === 2) {
				return attr(node, 'data-' + key);
			}

			attr(node, 'data-' + key, String(value));
		}
	}

	/**
	 * Checks if node matches the given selector.
	 *
	 * @param {?HTMLElement} node
	 * @param {string} selector
	 * @returns {boolean}
	 */
	function is(node, selector) {
		var result = false;

		if (node && node.nodeType === ELEMENT_NODE) {
			result = (node.matches || node.msMatchesSelector ||
				node.webkitMatchesSelector).call(node, selector);
		}

		return result;
	}


	/**
	 * Returns true if node contains child otherwise false.
	 *
	 * This differs from the DOM contains() method in that
	 * if node and child are equal this will return false.
	 *
	 * @param {!Node} node
	 * @param {HTMLElement} child
	 * @returns {boolean}
	 */
	function contains(node, child) {
		return node !== child && node.contains && node.contains(child);
	}

	/**
	 * @param {Node} node
	 * @param {string} [selector]
	 * @returns {?HTMLElement}
	 */
	function previousElementSibling(node, selector) {
		var prev = node.previousElementSibling;

		if (selector && prev) {
			return is(prev, selector) ? prev : null;
		}

		return prev;
	}

	/**
	 * @param {!Node} node
	 * @param {!Node} refNode
	 * @returns {Node}
	 */
	function insertBefore(node, refNode) {
		return refNode.parentNode.insertBefore(node, refNode);
	}

	/**
	 * @param {?HTMLElement} node
	 * @returns {!Array.<string>}
	 */
	function classes(node) {
		return node.className.trim().split(/\s+/);
	}

	/**
	 * @param {?HTMLElement} node
	 * @param {string} className
	 * @returns {boolean}
	 */
	function hasClass(node, className) {
		return is(node, '.' + className);
	}

	/**
	 * @param {!HTMLElement} node
	 * @param {string} className
	 */
	function addClass(node, className) {
		var classList = classes(node);

		if (classList.indexOf(className) < 0) {
			classList.push(className);
		}

		node.className = classList.join(' ');
	}

	/**
	 * @param {!HTMLElement} node
	 * @param {string} className
	 */
	function removeClass(node, className) {
		var classList = classes(node);

		arrayRemove(classList, className);

		node.className = classList.join(' ');
	}

	/**
	 * Toggles a class on node.
	 *
	 * If state is specified and is truthy it will add
	 * the class.
	 *
	 * If state is specified and is falsey it will remove
	 * the class.
	 *
	 * @param {HTMLElement} node
	 * @param {string} className
	 * @param {boolean} [state]
	 */
	function toggleClass(node, className, state) {
		state = isUndefined(state) ? !hasClass(node, className) : state;

		if (state) {
			addClass(node, className);
		} else {
			removeClass(node, className);
		}
	}

	/**
	 * Gets or sets the width of the passed node.
	 *
	 * @param {HTMLElement} node
	 * @param {number|string} [value]
	 * @returns {number|undefined}
	 */
	function width(node, value) {
		if (isUndefined(value)) {
			var cs = getComputedStyle(node);
			var padding = toFloat(cs.paddingLeft) + toFloat(cs.paddingRight);
			var border = toFloat(cs.borderLeftWidth) + toFloat(cs.borderRightWidth);

			return node.offsetWidth - padding - border;
		}

		css(node, 'width', value);
	}

	/**
	 * Gets or sets the height of the passed node.
	 *
	 * @param {HTMLElement} node
	 * @param {number|string} [value]
	 * @returns {number|undefined}
	 */
	function height(node, value) {
		if (isUndefined(value)) {
			var cs = getComputedStyle(node);
			var padding = toFloat(cs.paddingTop) + toFloat(cs.paddingBottom);
			var border = toFloat(cs.borderTopWidth) + toFloat(cs.borderBottomWidth);

			return node.offsetHeight - padding - border;
		}

		css(node, 'height', value);
	}

	/**
	 * Triggers a custom event with the specified name and
	 * sets the detail property to the data object passed.
	 *
	 * @param {HTMLElement} node
	 * @param {string} eventName
	 * @param {Object} [data]
	 */
	function trigger(node, eventName, data) {
		var event;

		if (isFunction(window.CustomEvent)) {
			event = new CustomEvent(eventName, {
				bubbles: true,
				cancelable: true,
				detail: data
			});
		} else {
			event = node.ownerDocument.createEvent('CustomEvent');
			event.initCustomEvent(eventName, true, true, data);
		}

		node.dispatchEvent(event);
	}

	/**
	 * Returns if a node is visible.
	 *
	 * @param {HTMLElement}
	 * @returns {boolean}
	 */
	function isVisible(node) {
		return !!node.getClientRects().length;
	}

	/**
	 * Convert CSS property names into camel case
	 *
	 * @param {string} string
	 * @returns {string}
	 */
	function camelCase(string) {
		return string
			.replace(/^-ms-/, 'ms-')
			.replace(/-(\w)/g, function (match, char) {
				return char.toUpperCase();
			});
	}


	/**
	 * Loop all child nodes of the passed node
	 *
	 * The function should accept 1 parameter being the node.
	 * If the function returns false the loop will be exited.
	 *
	 * @param  {HTMLElement} node
	 * @param  {function} func           Callback which is called with every
	 *                                   child node as the first argument.
	 * @param  {boolean} innermostFirst  If the innermost node should be passed
	 *                                   to the function before it's parents.
	 * @param  {boolean} siblingsOnly    If to only traverse the nodes siblings
	 * @param  {boolean} [reverse=false] If to traverse the nodes in reverse
	 */
	// eslint-disable-next-line max-params
	function traverse(node, func, innermostFirst, siblingsOnly, reverse) {
		node = reverse ? node.lastChild : node.firstChild;

		while (node) {
			var next = reverse ? node.previousSibling : node.nextSibling;

			if (
				(!innermostFirst && func(node) === false) ||
				(!siblingsOnly && traverse(
					node, func, innermostFirst, siblingsOnly, reverse
				) === false) ||
				(innermostFirst && func(node) === false)
			) {
				return false;
			}

			node = next;
		}
	}

	/**
	 * Like traverse but loops in reverse
	 * @see traverse
	 */
	function rTraverse(node, func, innermostFirst, siblingsOnly) {
		traverse(node, func, innermostFirst, siblingsOnly, true);
	}

	/**
	 * Parses HTML into a document fragment
	 *
	 * @param {string} html
	 * @param {Document} [context]
	 * @since 1.4.4
	 * @return {DocumentFragment}
	 */
	function parseHTML(html, context) {
		context = context || document;

		var ret = context.createDocumentFragment();
		var tmp = createElement('div', {}, context);

		tmp.innerHTML = html;

		while (tmp.firstChild) {
			appendChild(ret, tmp.firstChild);
		}

		return ret;
	}

	/**
	 * Checks if an element has any styling.
	 *
	 * It has styling if it is not a plain <div> or <p> or
	 * if it has a class, style attribute or data.
	 *
	 * @param  {HTMLElement} elm
	 * @return {boolean}
	 * @since 1.4.4
	 */
	function hasStyling(node) {
		return node && (!is(node, 'p,div') || node.className ||
			attr(node, 'style') || !isEmptyObject(data(node)));
	}

	/**
	 * Converts an element from one type to another.
	 *
	 * For example it can convert the element <b> to <strong>
	 *
	 * @param  {HTMLElement} element
	 * @param  {string}      toTagName
	 * @return {HTMLElement}
	 * @since 1.4.4
	 */
	function convertElement(element, toTagName) {
		var newElement = createElement(toTagName, {}, element.ownerDocument);

		each(element.attributes, function (_, attribute) {
			// Some browsers parse invalid attributes names like
			// 'size"2' which throw an exception when set, just
			// ignore these.
			try {
				attr(newElement, attribute.name, attribute.value);
			} catch (ex) { }
		});

		while (element.firstChild) {
			appendChild(newElement, element.firstChild);
		}

		element.parentNode.replaceChild(newElement, element);

		return newElement;
	}

	/**
	 * List of block level elements separated by bars (|)
	 *
	 * @type {string}
	 */
	var blockLevelList = '|body|hr|p|div|h1|h2|h3|h4|h5|h6|address|pre|' +
		'form|table|tbody|thead|tfoot|th|tr|td|li|ol|ul|blockquote|center|';

	/**
	 * List of elements that do not allow children separated by bars (|)
	 *
	 * @param {Node} node
	 * @return {boolean}
	 * @since  1.4.5
	 */
	function canHaveChildren(node) {
		// 1  = Element
		// 9  = Document
		// 11 = Document Fragment
		if (!/11?|9/.test(node.nodeType)) {
			return false;
		}

		// List of empty HTML tags separated by bar (|) character.
		// Source: http://www.w3.org/TR/html4/index/elements.html
		// Source: http://www.w3.org/TR/html5/syntax.html#void-elements
		return ('|iframe|area|base|basefont|br|col|frame|hr|img|input|wbr' +
			'|isindex|link|meta|param|command|embed|keygen|source|track|' +
			'object|').indexOf('|' + node.nodeName.toLowerCase() + '|') < 0;
	}

	/**
	 * Checks if an element is inline
	 *
	 * @param {HTMLElement} elm
	 * @param {boolean} [includeCodeAsBlock=false]
	 * @return {boolean}
	 */
	function isInline(elm, includeCodeAsBlock) {
		var tagName,
			nodeType = (elm || {}).nodeType || TEXT_NODE;

		if (nodeType !== ELEMENT_NODE) {
			return nodeType === TEXT_NODE;
		}

		tagName = elm.tagName.toLowerCase();

		if (tagName === 'code') {
			return !includeCodeAsBlock;
		}

		return blockLevelList.indexOf('|' + tagName + '|') < 0;
	}

	/**
	 * Copy the CSS from 1 node to another.
	 *
	 * Only copies CSS defined on the element e.g. style attr.
	 *
	 * @param {HTMLElement} from
	 * @param {HTMLElement} to
	 */
	function copyCSS(from, to) {
		to.style.cssText = from.style.cssText + to.style.cssText;
	}

	/**
	 * Fixes block level elements inside in inline elements.
	 *
	 * Also fixes invalid list nesting by placing nested lists
	 * inside the previous li tag or wrapping them in an li tag.
	 *
	 * @param {HTMLElement} node
	 */
	function fixNesting(node) {
		var getLastInlineParent = function (node) {
			while (isInline(node.parentNode, true)) {
				node = node.parentNode;
			}

			return node;
		};

		traverse(node, function (node) {
			var list = 'ul,ol',
				isBlock = !isInline(node, true);

			// Any blocklevel element inside an inline element needs fixing.
			if (isBlock && isInline(node.parentNode, true)) {
				var parent = getLastInlineParent(node),
					before = extractContents(parent, node),
					middle = node;

				// copy current styling so when moved out of the parent
				// it still has the same styling
				copyCSS(parent, middle);

				insertBefore(before, parent);
				insertBefore(middle, parent);
			}

			// Fix invalid nested lists which should be wrapped in an li tag
			if (isBlock && is(node, list) && is(node.parentNode, list)) {
				var li = previousElementSibling(node, 'li');

				if (!li) {
					li = createElement('li');
					insertBefore(li, node);
				}

				appendChild(li, node);
			}
		});
	}

	/**
	 * Finds the common parent of two nodes
	 *
	 * @param {!HTMLElement} node1
	 * @param {!HTMLElement} node2
	 * @return {?HTMLElement}
	 */
	function findCommonAncestor(node1, node2) {
		while ((node1 = node1.parentNode)) {
			if (contains(node1, node2)) {
				return node1;
			}
		}
	}

	/**
	 * @param {?Node}
	 * @param {boolean} [previous=false]
	 * @returns {?Node}
	 */
	function getSibling(node, previous) {
		if (!node) {
			return null;
		}

		return (previous ? node.previousSibling : node.nextSibling) ||
			getSibling(node.parentNode, previous);
	}

	/**
	 * Removes unused whitespace from the root and all it's children.
	 *
	 * @param {!HTMLElement} root
	 * @since 1.4.3
	 */
	function removeWhiteSpace(root) {
		var nodeValue, nodeType, next, previous, previousSibling,
			nextNode, trimStart,
			cssWhiteSpace = css(root, 'whiteSpace'),
			// Preserve newlines if is pre-line
			preserveNewLines = /line$/i.test(cssWhiteSpace),
			node = root.firstChild;

		// Skip pre & pre-wrap with any vendor prefix
		if (/pre(\-wrap)?$/i.test(cssWhiteSpace)) {
			return;
		}

		while (node) {
			nextNode = node.nextSibling;
			nodeValue = node.nodeValue;
			nodeType = node.nodeType;

			if (nodeType === ELEMENT_NODE && node.firstChild) {
				removeWhiteSpace(node);
			}

			if (nodeType === TEXT_NODE) {
				next = getSibling(node);
				previous = getSibling(node, true);
				trimStart = false;

				while (hasClass(previous, 'sceditor-ignore')) {
					previous = getSibling(previous, true);
				}

				// If previous sibling isn't inline or is a textnode that
				// ends in whitespace, time the start whitespace
				if (isInline(node) && previous) {
					previousSibling = previous;

					while (previousSibling.lastChild) {
						previousSibling = previousSibling.lastChild;

						// eslint-disable-next-line max-depth
						while (hasClass(previousSibling, 'sceditor-ignore')) {
							previousSibling = getSibling(previousSibling, true);
						}
					}

					trimStart = previousSibling.nodeType === TEXT_NODE ?
						/[\t\n\r ]$/.test(previousSibling.nodeValue) :
						!isInline(previousSibling);
				}

				// Clear zero width spaces
				nodeValue = nodeValue.replace(/\u200B/g, '');

				// Strip leading whitespace
				if (!previous || !isInline(previous) || trimStart) {
					nodeValue = nodeValue.replace(
						preserveNewLines ? /^[\t ]+/ : /^[\t\n\r ]+/,
						''
					);
				}

				// Strip trailing whitespace
				if (!next || !isInline(next)) {
					nodeValue = nodeValue.replace(
						preserveNewLines ? /[\t ]+$/ : /[\t\n\r ]+$/,
						''
					);
				}

				// Remove empty text nodes
				if (!nodeValue.length) {
					remove(node);
				} else {
					node.nodeValue = nodeValue.replace(
						preserveNewLines ? /[\t ]+/g : /[\t\n\r ]+/g,
						' '
					);
				}
			}

			node = nextNode;
		}
	}

	/**
	 * Extracts all the nodes between the start and end nodes
	 *
	 * @param {HTMLElement} startNode	The node to start extracting at
	 * @param {HTMLElement} endNode		The node to stop extracting at
	 * @return {DocumentFragment}
	 */
	function extractContents(startNode, endNode) {
		var range = startNode.ownerDocument.createRange();

		range.setStartBefore(startNode);
		range.setEndAfter(endNode);

		return range.extractContents();
	}

	/**
	 * Gets the offset position of an element
	 *
	 * @param  {HTMLElement} node
	 * @return {Object} An object with left and top properties
	 */
	function getOffset(node) {
		var left = 0,
			top = 0;

		while (node) {
			left += node.offsetLeft;
			top += node.offsetTop;
			node = node.offsetParent;
		}

		return {
			left: left,
			top: top
		};
	}

	/**
	 * Gets the value of a CSS property from the elements style attribute
	 *
	 * @param  {HTMLElement} elm
	 * @param  {string} property
	 * @return {string}
	 */
	function getStyle(elm, property) {
		var direction, styleValue,
			elmStyle = elm.style;

		if (!cssPropertyNameCache[property]) {
			cssPropertyNameCache[property] = camelCase(property);
		}

		property = cssPropertyNameCache[property];
		styleValue = elmStyle[property];

		// Add an exception for text-align
		if ('textAlign' === property) {
			direction = elmStyle.direction;
			styleValue = styleValue || css(elm, property);

			if (css(elm.parentNode, property) === styleValue ||
				css(elm, 'display') !== 'block' || is(elm, 'hr,th')) {
				return '';
			}

			// IE changes text-align to the same as the current direction
			// so skip unless its not the same
			if ((/right/i.test(styleValue) && direction === 'rtl') ||
				(/left/i.test(styleValue) && direction === 'ltr')) {
				return '';
			}
		}

		return styleValue;
	}

	/**
	 * Tests if an element has a style.
	 *
	 * If values are specified it will check that the styles value
	 * matches one of the values
	 *
	 * @param  {HTMLElement} elm
	 * @param  {string} property
	 * @param  {string|array} [values]
	 * @return {boolean}
	 */
	function hasStyle(elm, property, values) {
		var styleValue = getStyle(elm, property);

		if (!styleValue) {
			return false;
		}

		return !values || styleValue === values ||
			(Array.isArray(values) && values.indexOf(styleValue) > -1);
	}

	/**
	 * Default options for SCEditor
	 * @type {Object}
	 */
	var defaultOptions = {
		/** @lends jQuery.sceditor.defaultOptions */
		/**
		 * Toolbar buttons order and groups. Should be comma separated and
		 * have a bar | to separate groups
		 *
		 * @type {string}
		 */
		toolbar: 'bold,italic,underline,strike,subscript,superscript|' +
			'left,center,right,justify|font,size,color,removeformat|' +
			'cut,copy,pastetext|bulletlist,orderedlist,indent,outdent|' +
			'table|code,quote|horizontalrule,image,email,link,unlink|' +
			'emoticon,youtube,date,time|ltr,rtl|print,maximize,source',

		/**
		 * Comma separated list of commands to excludes from the toolbar
		 *
		 * @type {string}
		 */
		toolbarExclude: null,

		/**
		 * Stylesheet to include in the WYSIWYG editor. This is what will style
		 * the WYSIWYG elements
		 *
		 * @type {string}
		 */
		style: 'jquery.sceditor.default.css',

		/**
		 * Comma separated list of fonts for the font selector
		 *
		 * @type {string}
		 */
		fonts: 'Arial,Arial Black,Comic Sans MS,Courier New,Georgia,Impact,' +
			'Sans-serif,Serif,Times New Roman,Trebuchet MS,Verdana',

		/**
		 * Colors should be comma separated and have a bar | to signal a new
		 * column.
		 *
		 * If null the colors will be auto generated.
		 *
		 * @type {string}
		 */
		colors: '#000000,#44B8FF,#1E92F7,#0074D9,#005DC2,#00369B,#b3d5f4|' +
			'#444444,#C3FFFF,#9DF9FF,#7FDBFF,#68C4E8,#419DC1,#d9f4ff|' +
			'#666666,#72FF84,#4CEA5E,#2ECC40,#17B529,#008E02,#c0f0c6|' +
			'#888888,#FFFF44,#FFFA1E,#FFDC00,#E8C500,#C19E00,#fff5b3|' +
			'#aaaaaa,#FFC95F,#FFA339,#FF851B,#E86E04,#C14700,#ffdbbb|' +
			'#cccccc,#FF857A,#FF5F54,#FF4136,#E82A1F,#C10300,#ffc6c3|' +
			'#eeeeee,#FF56FF,#FF30DC,#F012BE,#D900A7,#B20080,#fbb8ec|' +
			'#ffffff,#F551FF,#CF2BE7,#B10DC9,#9A00B2,#9A00B2,#e8b6ef',

		/**
		 * The locale to use.
		 * @type {string}
		 */
		locale: attr(document.documentElement, 'lang') || 'en',

		/**
		 * The Charset to use
		 * @type {string}
		 */
		charset: 'utf-8',

		/**
		 * Compatibility mode for emoticons.
		 *
		 * Helps if you have emoticons such as :/ which would put an emoticon
		 * inside http://
		 *
		 * This mode requires emoticons to be surrounded by whitespace or end of
		 * line chars. This mode has limited As You Type emoticon conversion
		 * support. It will not replace AYT for end of line chars, only
		 * emoticons surrounded by whitespace. They will still be replaced
		 * correctly when loaded just not AYT.
		 *
		 * @type {boolean}
		 */
		emoticonsCompat: false,

		/**
		 * If to enable emoticons. Can be changes at runtime using the
		 * emoticons() method.
		 *
		 * @type {boolean}
		 * @since 1.4.2
		 */
		emoticonsEnabled: true,

		/**
		 * Emoticon root URL
		 *
		 * @type {string}
		 */
		emoticonsRoot: '',
		emoticons: {
			dropdown: {
				':)': 'emoticons/smile.png',
				':angel:': 'emoticons/angel.png',
				':angry:': 'emoticons/angry.png',
				'8-)': 'emoticons/cool.png',
				':\'(': 'emoticons/cwy.png',
				':ermm:': 'emoticons/ermm.png',
				':D': 'emoticons/grin.png',
				'<3': 'emoticons/heart.png',
				':(': 'emoticons/sad.png',
				':O': 'emoticons/shocked.png',
				':P': 'emoticons/tongue.png',
				';)': 'emoticons/wink.png'
			},
			more: {
				':alien:': 'emoticons/alien.png',
				':blink:': 'emoticons/blink.png',
				':blush:': 'emoticons/blush.png',
				':cheerful:': 'emoticons/cheerful.png',
				':devil:': 'emoticons/devil.png',
				':dizzy:': 'emoticons/dizzy.png',
				':getlost:': 'emoticons/getlost.png',
				':happy:': 'emoticons/happy.png',
				':kissing:': 'emoticons/kissing.png',
				':ninja:': 'emoticons/ninja.png',
				':pinch:': 'emoticons/pinch.png',
				':pouty:': 'emoticons/pouty.png',
				':sick:': 'emoticons/sick.png',
				':sideways:': 'emoticons/sideways.png',
				':silly:': 'emoticons/silly.png',
				':sleeping:': 'emoticons/sleeping.png',
				':unsure:': 'emoticons/unsure.png',
				':woot:': 'emoticons/w00t.png',
				':wassat:': 'emoticons/wassat.png'
			},
			hidden: {
				':whistling:': 'emoticons/whistling.png',
				':love:': 'emoticons/wub.png'
			}
		},

		/**
		 * Width of the editor. Set to null for automatic with
		 *
		 * @type {?number}
		 */
		width: null,

		/**
		 * Height of the editor including toolbar. Set to null for automatic
		 * height
		 *
		 * @type {?number}
		 */
		height: null,

		/**
		 * If to allow the editor to be resized
		 *
		 * @type {boolean}
		 */
		resizeEnabled: true,

		/**
		 * Min resize to width, set to null for half textarea width or -1 for
		 * unlimited
		 *
		 * @type {?number}
		 */
		resizeMinWidth: null,
		/**
		 * Min resize to height, set to null for half textarea height or -1 for
		 * unlimited
		 *
		 * @type {?number}
		 */
		resizeMinHeight: null,
		/**
		 * Max resize to height, set to null for double textarea height or -1
		 * for unlimited
		 *
		 * @type {?number}
		 */
		resizeMaxHeight: null,
		/**
		 * Max resize to width, set to null for double textarea width or -1 for
		 * unlimited
		 *
		 * @type {?number}
		 */
		resizeMaxWidth: null,
		/**
		 * If resizing by height is enabled
		 *
		 * @type {boolean}
		 */
		resizeHeight: true,
		/**
		 * If resizing by width is enabled
		 *
		 * @type {boolean}
		 */
		resizeWidth: true,

		/**
		 * Date format, will be overridden if locale specifies one.
		 *
		 * The words year, month and day will be replaced with the users current
		 * year, month and day.
		 *
		 * @type {string}
		 */
		dateFormat: 'year-month-day',

		/**
		 * Element to inset the toolbar into.
		 *
		 * @type {HTMLElement}
		 */
		toolbarContainer: null,

		/**
		 * If to enable paste filtering. This is currently experimental, please
		 * report any issues.
		 *
		 * @type {boolean}
		 */
		enablePasteFiltering: false,

		/**
		 * If to completely disable pasting into the editor
		 *
		 * @type {boolean}
		 */
		disablePasting: false,

		/**
		 * If the editor is read only.
		 *
		 * @type {boolean}
		 */
		readOnly: false,

		/**
		 * If to set the editor to right-to-left mode.
		 *
		 * If set to null the direction will be automatically detected.
		 *
		 * @type {boolean}
		 */
		rtl: false,

		/**
		 * If to auto focus the editor on page load
		 *
		 * @type {boolean}
		 */
		autofocus: false,

		/**
		 * If to auto focus the editor to the end of the content
		 *
		 * @type {boolean}
		 */
		autofocusEnd: true,

		/**
		 * If to auto expand the editor to fix the content
		 *
		 * @type {boolean}
		 */
		autoExpand: false,

		/**
		 * If to auto update original textbox on blur
		 *
		 * @type {boolean}
		 */
		autoUpdate: false,

		/**
		 * If to enable the browsers built in spell checker
		 *
		 * @type {boolean}
		 */
		spellcheck: true,

		/**
		 * If to run the source editor when there is no WYSIWYG support. Only
		 * really applies to mobile OS's.
		 *
		 * @type {boolean}
		 */
		runWithoutWysiwygSupport: false,

		/**
		 * If to load the editor in source mode and still allow switching
		 * between WYSIWYG and source mode
		 *
		 * @type {boolean}
		 */
		startInSourceMode: false,

		/**
		 * Optional ID to give the editor.
		 *
		 * @type {string}
		 */
		id: null,

		/**
		 * Comma separated list of plugins
		 *
		 * @type {string}
		 */
		plugins: '',

		/**
		 * z-index to set the editor container to. Needed for jQuery UI dialog.
		 *
		 * @type {?number}
		 */
		zIndex: null,

		/**
		 * If to trim the BBCode. Removes any spaces at the start and end of the
		 * BBCode string.
		 *
		 * @type {boolean}
		 */
		bbcodeTrim: false,

		/**
		 * If to disable removing block level elements by pressing backspace at
		 * the start of them
		 *
		 * @type {boolean}
		 */
		disableBlockRemove: false,

		/**
		 * BBCode parser options, only applies if using the editor in BBCode
		 * mode.
		 *
		 * See SCEditor.BBCodeParser.defaults for list of valid options
		 *
		 * @type {Object}
		 */
		parserOptions: {},

		/**
		 * CSS that will be added to the to dropdown menu (eg. z-index)
		 *
		 * @type {Object}
		 */
		dropDownCss: {}
	};

	var USER_AGENT = navigator.userAgent;

	/**
	 * Detects the version of IE is being used if any.
	 *
	 * Will be the IE version number or undefined if the
	 * browser is not IE.
	 *
	 * Source: https://gist.github.com/527683 with extra code
	 * for IE 10 & 11 detection.
	 *
	 * @function
	 * @name ie
	 * @type {number}
	 */
	var ie = (function () {
		var undef,
			v = 3,
			doc = document,
			div = doc.createElement('div'),
			all = div.getElementsByTagName('i');

		do {
			div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->';
		} while (all[0]);

		// Detect IE 10 as it doesn't support conditional comments.
		if ((doc.documentMode && doc.all && window.atob)) {
			v = 10;
		}

		// Detect IE 11
		if (v === 4 && doc.documentMode) {
			v = 11;
		}

		return v > 4 ? v : undef;
	}());

	var edge = '-ms-ime-align' in document.documentElement.style;

	/**
	 * Detects if the browser is iOS
	 *
	 * Needed to fix iOS specific bugs
	 *
	 * @function
	 * @name ios
	 * @memberOf jQuery.sceditor
	 * @type {boolean}
	 */
	var ios = /iPhone|iPod|iPad| wosbrowser\//i.test(USER_AGENT);

	/**
	 * If the browser supports WYSIWYG editing (e.g. older mobile browsers).
	 *
	 * @function
	 * @name isWysiwygSupported
	 * @return {boolean}
	 */
	var isWysiwygSupported = (function () {
		var match, isUnsupported;

		var div = document.createElement('div');
		div.contentEditable = true;

		// Check if the contentEditable attribute is supported
		if (!('contentEditable' in document.documentElement) ||
			div.contentEditable !== 'true') {
			return false;
		}

		// I think blackberry supports contentEditable or will at least
		// give a valid value for the contentEditable detection above
		// so it isn't included in the below tests.

		// I hate having to do UA sniffing but some mobile browsers say they
		// support contentediable when it isn't usable, i.e. you can't enter
		// text.
		// This is the only way I can think of to detect them which is also how
		// every other editor I've seen deals with this issue.

		// Exclude Opera mobile and mini
		isUnsupported = /Opera Mobi|Opera Mini/i.test(USER_AGENT);

		if (/Android/i.test(USER_AGENT)) {
			isUnsupported = true;

			if (/Safari/.test(USER_AGENT)) {
				// Android browser 534+ supports content editable
				// This also matches Chrome which supports content editable too
				match = /Safari\/(\d+)/.exec(USER_AGENT);
				isUnsupported = (!match || !match[1] ? true : match[1] < 534);
			}
		}

		// The current version of Amazon Silk supports it, older versions didn't
		// As it uses webkit like Android, assume it's the same and started
		// working at versions >= 534
		if (/ Silk\//i.test(USER_AGENT)) {
			match = /AppleWebKit\/(\d+)/.exec(USER_AGENT);
			isUnsupported = (!match || !match[1] ? true : match[1] < 534);
		}

		// iOS 5+ supports content editable
		if (ios) {
			// Block any version <= 4_x(_x)
			isUnsupported = /OS [0-4](_\d)+ like Mac/i.test(USER_AGENT);
		}

		// Firefox does support WYSIWYG on mobiles so override
		// any previous value if using FF
		if (/Firefox/i.test(USER_AGENT)) {
			isUnsupported = false;
		}

		if (/OneBrowser/i.test(USER_AGENT)) {
			isUnsupported = false;
		}

		// UCBrowser works but doesn't give a unique user agent
		if (navigator.vendor === 'UCWEB') {
			isUnsupported = false;
		}

		// IE <= 9 is not supported any more
		if (ie <= 9) {
			isUnsupported = true;
		}

		return !isUnsupported;
	}());

	// Must start with a valid scheme
	// 		^
	// Schemes that are considered safe
	// 		(https?|s?ftp|mailto|spotify|skype|ssh|teamspeak|tel):|
	// Relative schemes (//:) are considered safe
	// 		(\\/\\/)|
	// Image data URI's are considered safe
	// 		data:image\\/(png|bmp|gif|p?jpe?g);
	var VALID_SCHEME_REGEX =
		/^(https?|s?ftp|mailto|spotify|skype|ssh|teamspeak|tel):|(\/\/)|data:image\/(png|bmp|gif|p?jpe?g);/i;

	/**
	 * Escapes a string so it's safe to use in regex
	 *
	 * @param {string} str
	 * @return {string}
	 */
	function regex(str) {
		return str.replace(/([\-.*+?^=!:${}()|\[\]\/\\])/g, '\\$1');
	}

	/**
	 * Escapes all HTML entities in a string
	 *
	 * If noQuotes is set to false, all single and double
	 * quotes will also be escaped
	 *
	 * @param {string} str
	 * @param {boolean} [noQuotes=true]
	 * @return {string}
	 * @since 1.4.1
	 */
	function entities(str, noQuotes) {
		if (!str) {
			return str;
		}

		var replacements = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'  ': '&nbsp; ',
			'\r\n': '<br />',
			'\r': '<br />',
			'\n': '<br />'
		};

		if (noQuotes !== false) {
			replacements['"'] = '&#34;';
			replacements['\''] = '&#39;';
			replacements['`'] = '&#96;';
		}

		str = str.replace(/ {2}|\r\n|[&<>\r\n'"`]/g, function (match) {
			return replacements[match] || match;
		});

		return str;
	}

	/**
	 * Escape URI scheme.
	 *
	 * Appends the current URL to a url if it has a scheme that is not:
	 *
	 * http
	 * https
	 * sftp
	 * ftp
	 * mailto
	 * spotify
	 * skype
	 * ssh
	 * teamspeak
	 * tel
	 * //
	 * data:image/(png|jpeg|jpg|pjpeg|bmp|gif);
	 *
	 * **IMPORTANT**: This does not escape any HTML in a url, for
	 * that use the escape.entities() method.
	 *
	 * @param  {string} url
	 * @return {string}
	 * @since 1.4.5
	 */
	function uriScheme(url) {
		var path,
			// If there is a : before a / then it has a scheme
			hasScheme = /^[^\/]*:/i,
			location = window.location;

		// Has no scheme or a valid scheme
		if ((!url || !hasScheme.test(url)) || VALID_SCHEME_REGEX.test(url)) {
			return url;
		}

		path = location.pathname.split('/');
		path.pop();

		return location.protocol + '//' +
			location.host +
			path.join('/') + '/' +
			url;
	}

	/**
	 * HTML templates used by the editor and default commands
	 * @type {Object}
	 * @private
	 */
	var _templates = {
		html:
			'<!DOCTYPE html>' +
			'<html{attrs}>' +
			'<head>' +
			'<style>.ie * {min-height: auto !important} ' +
			'.ie table td {height:15px} ' +
			// Target Edge (fixes edge issues)
			'@supports (-ms-ime-align:auto) { ' +
			'* { min-height: auto !important; } ' +
			'}' +
			'</style>' +
			'<meta http-equiv="Content-Type" ' +
			'content="text/html;charset={charset}" />' +
			'<link rel="stylesheet" type="text/css" href="{style}" />' +
			'</head>' +
			'<body contenteditable="true" {spellcheck}><p></p></body>' +
			'</html>',

		toolbarButton: '<a class="sceditor-button sceditor-button-{name}" ' +
			'data-sceditor-command="{name}" unselectable="on">' +
			'<div unselectable="on">{dispName}</div></a>',

		emoticon: '<img src="{url}" data-sceditor-emoticon="{key}" ' +
			'alt="{key}" title="{tooltip}" />',

		fontOpt: '<a class="sceditor-font-option" href="#" ' +
			'data-font="{font}"><font face="{font}">{font}</font></a>',

		sizeOpt: '<a class="sceditor-fontsize-option" data-size="{size}" ' +
			'href="#"><font size="{size}">{size}</font></a>',

		pastetext:
			'<div><label for="txt">{label}</label> ' +
			'<textarea cols="20" rows="7" id="txt"></textarea></div>' +
			'<div><input type="button" class="button" value="{insert}" />' +
			'</div>',

		table:
			'<div><label for="rows">{rows}</label><input type="text" ' +
			'id="rows" value="2" /></div>' +
			'<div><label for="cols">{cols}</label><input type="text" ' +
			'id="cols" value="2" /></div>' +
			'<div><input type="button" class="button" value="{insert}"' +
			' /></div>',

		image:
			'<div><label for="link">{url}</label> ' +
			'<input type="text" id="image" dir="ltr" placeholder="https://" /></div>' +
			'<div><label for="width">{width}</label> ' +
			'<input type="text" id="width" size="2" dir="ltr" /></div>' +
			'<div><label for="height">{height}</label> ' +
			'<input type="text" id="height" size="2" dir="ltr" /></div>' +
			'<div><input type="button" class="button" value="{insert}" />' +
			'</div>',

		email:
			'<div><label for="email">{label}</label> ' +
			'<input type="text" id="email" dir="ltr" /></div>' +
			'<div><label for="des">{desc}</label> ' +
			'<input type="text" id="des" /></div>' +
			'<div><input type="button" class="button" value="{insert}" />' +
			'</div>',

		link:
			'<div><label for="link">{url}</label> ' +
			'<input type="text" id="link" dir="ltr" placeholder="https://" /></div>' +
			'<div><label for="des">{desc}</label> ' +
			'<input type="text" id="des" /></div>' +
			'<div><input type="button" class="button" value="{ins}" /></div>',

		youtubeMenu:
			'<div><label for="link">{label}</label> ' +
			'<input type="text" id="link" dir="ltr" placeholder="https://" /></div>' +
			'<div><input type="button" class="button" value="{insert}" />' +
			'</div>',

		youtube:
			'<iframe width="560" height="315" frameborder="0" allowfullscreen ' +
			'src="https://www.youtube.com/embed/{id}?wmode=opaque&start={time}" ' +
			'data-youtube-id="{id}"></iframe>'
	};

	/**
	 * Replaces any params in a template with the passed params.
	 *
	 * If createHtml is passed it will return a DocumentFragment
	 * containing the parsed template.
	 *
	 * @param {string} name
	 * @param {Object} [params]
	 * @param {boolean} [createHtml]
	 * @returns {string|DocumentFragment}
	 * @private
	 */
	function _tmpl(name, params, createHtml) {
		var template = _templates[name];

		Object.keys(params).forEach(function (name) {
			template = template.replace(
				new RegExp(regex('{' + name + '}'), 'g'), params[name]
			);
		});

		if (createHtml) {
			template = parseHTML(template);
		}

		return template;
	}

	// In IE < 11 a BR at the end of a block level element
	// causes a line break. In all other browsers it's collapsed.
	var IE_BR_FIX = ie && ie < 11;

	/**
	 * Fixes a bug in FF where it sometimes wraps
	 * new lines in their own list item.
	 * See issue #359
	 */
	function fixFirefoxListBug(editor) {
		// Only apply to Firefox as will break other browsers.
		if ('mozHidden' in document) {
			var node = editor.getBody();
			var next;

			while (node) {
				next = node;

				if (next.firstChild) {
					next = next.firstChild;
				} else {

					while (next && !next.nextSibling) {
						next = next.parentNode;
					}

					if (next) {
						next = next.nextSibling;
					}
				}

				if (node.nodeType === 3 && /[\n\r\t]+/.test(node.nodeValue)) {
					// Only remove if newlines are collapsed
					if (!/^pre/.test(css(node.parentNode, 'whiteSpace'))) {
						remove(node);
					}
				}

				node = next;
			}
		}
	}


	/**
	 * Map of all the commands for SCEditor
	 * @type {Object}
	 * @name commands
	 * @memberOf jQuery.sceditor
	 */
	var defaultCmds = {
		// START_COMMAND: Bold
		bold: {
			exec: 'bold',
			tooltip: 'Bold',
			shortcut: 'Ctrl+B'
		},
		// END_COMMAND
		// START_COMMAND: Italic
		italic: {
			exec: 'italic',
			tooltip: 'Italic',
			shortcut: 'Ctrl+I'
		},
		// END_COMMAND
		// START_COMMAND: Underline
		underline: {
			exec: 'underline',
			tooltip: 'Underline',
			shortcut: 'Ctrl+U'
		},
		// END_COMMAND
		// START_COMMAND: Strikethrough
		strike: {
			exec: 'strikethrough',
			tooltip: 'Strikethrough'
		},
		// END_COMMAND
		// START_COMMAND: Subscript
		subscript: {
			exec: 'subscript',
			tooltip: 'Subscript'
		},
		// END_COMMAND
		// START_COMMAND: Superscript
		superscript: {
			exec: 'superscript',
			tooltip: 'Superscript'
		},
		// END_COMMAND

		// START_COMMAND: Left
		left: {
			state: function (node) {
				if (node && node.nodeType === 3) {
					node = node.parentNode;
				}

				if (node) {
					var isLtr = css(node, 'direction') === 'ltr';
					var align = css(node, 'textAlign');

					return align === 'left' || align === (isLtr ? 'start' : 'end');
				}
			},
			exec: 'justifyleft',
			tooltip: 'Align left'
		},
		// END_COMMAND
		// START_COMMAND: Centre
		center: {
			exec: 'justifycenter',
			tooltip: 'Center'
		},
		// END_COMMAND
		// START_COMMAND: Right
		right: {
			state: function (node) {
				if (node && node.nodeType === 3) {
					node = node.parentNode;
				}

				if (node) {
					var isLtr = css(node, 'direction') === 'ltr';
					var align = css(node, 'textAlign');

					return align === 'right' || align === (isLtr ? 'end' : 'start');
				}
			},
			exec: 'justifyright',
			tooltip: 'Align right'
		},
		// END_COMMAND
		// START_COMMAND: Justify
		justify: {
			exec: 'justifyfull',
			tooltip: 'Justify'
		},
		// END_COMMAND

		// START_COMMAND: Font
		font: {
			_dropDown: function (editor, caller, callback) {
				var content = createElement('div');

				on(content, 'click', 'a', function (e) {
					callback(data(this, 'font'));
					editor.closeDropDown(true);
					e.preventDefault();
				});

				editor.opts.fonts.split(',').forEach(function (font) {
					appendChild(content, _tmpl('fontOpt', {
						font: font
					}, true));
				});

				editor.createDropDown(caller, 'font-picker', content);
			},
			exec: function (caller) {
				var editor = this;

				defaultCmds.font._dropDown(editor, caller, function (fontName) {
					editor.execCommand('fontname', fontName);
				});
			},
			tooltip: 'Font Name'
		},
		// END_COMMAND
		// START_COMMAND: Size
		size: {
			_dropDown: function (editor, caller, callback) {
				var content = createElement('div');

				on(content, 'click', 'a', function (e) {
					callback(data(this, 'size'));
					editor.closeDropDown(true);
					e.preventDefault();
				});

				for (var i = 1; i <= 7; i++) {
					appendChild(content, _tmpl('sizeOpt', {
						size: i
					}, true));
				}

				editor.createDropDown(caller, 'fontsize-picker', content);
			},
			exec: function (caller) {
				var editor = this;

				defaultCmds.size._dropDown(editor, caller, function (fontSize) {
					editor.execCommand('fontsize', fontSize);
				});
			},
			tooltip: 'Font Size'
		},
		// END_COMMAND
		// START_COMMAND: Colour
		color: {
			_dropDown: function (editor, caller, callback) {
				var content = createElement('div'),
					html = '',
					cmd = defaultCmds.color;

				if (!cmd._htmlCache) {
					editor.opts.colors.split('|').forEach(function (column) {
						html += '<div class="sceditor-color-column">';

						column.split(',').forEach(function (color) {
							html +=
								'<a href="#" class="sceditor-color-option"' +
								' style="background-color: ' + color + '"' +
								' data-color="' + color + '"></a>';
						});

						html += '</div>';
					});

					cmd._htmlCache = html;
				}

				appendChild(content, parseHTML(cmd._htmlCache));

				on(content, 'click', 'a', function (e) {
					callback(data(this, 'color'));
					editor.closeDropDown(true);
					e.preventDefault();
				});

				editor.createDropDown(caller, 'color-picker', content);
			},
			exec: function (caller) {
				var editor = this;

				defaultCmds.color._dropDown(editor, caller, function (color) {
					editor.execCommand('forecolor', color);
				});
			},
			tooltip: 'Font Color'
		},
		// END_COMMAND
		// START_COMMAND: Remove Format
		removeformat: {
			exec: 'removeformat',
			tooltip: 'Remove Formatting'
		},
		// END_COMMAND

		// START_COMMAND: Cut
		cut: {
			exec: 'cut',
			tooltip: 'Cut',
			errorMessage: 'Your browser does not allow the cut command. ' +
				'Please use the keyboard shortcut Ctrl/Cmd-X'
		},
		// END_COMMAND
		// START_COMMAND: Copy
		copy: {
			exec: 'copy',
			tooltip: 'Copy',
			errorMessage: 'Your browser does not allow the copy command. ' +
				'Please use the keyboard shortcut Ctrl/Cmd-C'
		},
		// END_COMMAND
		// START_COMMAND: Paste
		paste: {
			exec: 'paste',
			tooltip: 'Paste',
			errorMessage: 'Your browser does not allow the paste command. ' +
				'Please use the keyboard shortcut Ctrl/Cmd-V'
		},
		// END_COMMAND
		// START_COMMAND: Paste Text
		pastetext: {
			exec: function (caller) {
				var val,
					content = createElement('div'),
					editor = this;

				appendChild(content, _tmpl('pastetext', {
					label: editor._(
						'Paste your text inside the following box:'
					),
					insert: editor._('Insert')
				}, true));

				on(content, 'click', '.button', function (e) {
					val = find(content, '#txt')[0].value;

					if (val) {
						editor.wysiwygEditorInsertText(val);
					}

					editor.closeDropDown(true);
					e.preventDefault();
				});

				editor.createDropDown(caller, 'pastetext', content);
			},
			tooltip: 'Paste Text'
		},
		// END_COMMAND
		// START_COMMAND: Bullet List
		bulletlist: {
			exec: function () {
				fixFirefoxListBug(this);
				this.execCommand('insertunorderedlist');
			},
			tooltip: 'Bullet list'
		},
		// END_COMMAND
		// START_COMMAND: Ordered List
		orderedlist: {
			exec: function () {
				fixFirefoxListBug(this);
				this.execCommand('insertorderedlist');
			},
			tooltip: 'Numbered list'
		},
		// END_COMMAND
		// START_COMMAND: Indent
		indent: {
			state: function (parent$$1, firstBlock) {
				// Only works with lists, for now
				var range, startParent, endParent;

				if (is(firstBlock, 'li')) {
					return 0;
				}

				if (is(firstBlock, 'ul,ol,menu')) {
					// if the whole list is selected, then this must be
					// invalidated because the browser will place a
					// <blockquote> there
					range = this.getRangeHelper().selectedRange();

					startParent = range.startContainer.parentNode;
					endParent = range.endContainer.parentNode;

					// TODO: could use nodeType for this?
					// Maybe just check the firstBlock contains both the start
					//and end containers

					// Select the tag, not the textNode
					// (that's why the parentNode)
					if (startParent !==
						startParent.parentNode.firstElementChild ||
						// work around a bug in FF
						(is(endParent, 'li') && endParent !==
							endParent.parentNode.lastElementChild)) {
						return 0;
					}
				}

				return -1;
			},
			exec: function () {
				var editor = this,
					block = editor.getRangeHelper().getFirstBlockParent();

				editor.focus();

				// An indent system is quite complicated as there are loads
				// of complications and issues around how to indent text
				// As default, let's just stay with indenting the lists,
				// at least, for now.
				if (closest(block, 'ul,ol,menu')) {
					editor.execCommand('indent');
				}
			},
			tooltip: 'Add indent'
		},
		// END_COMMAND
		// START_COMMAND: Outdent
		outdent: {
			state: function (parents$$1, firstBlock) {
				return closest(firstBlock, 'ul,ol,menu') ? 0 : -1;
			},
			exec: function () {
				var block = this.getRangeHelper().getFirstBlockParent();
				if (closest(block, 'ul,ol,menu')) {
					this.execCommand('outdent');
				}
			},
			tooltip: 'Remove one indent'
		},
		// END_COMMAND

		// START_COMMAND: Table
		table: {
			exec: function (caller) {
				var editor = this,
					content = createElement('div');

				appendChild(content, _tmpl('table', {
					rows: editor._('Rows:'),
					cols: editor._('Cols:'),
					insert: editor._('Insert')
				}, true));

				on(content, 'click', '.button', function (e) {
					var rows = Number(find(content, '#rows')[0].value),
						cols = Number(find(content, '#cols')[0].value),
						html = '<table>';

					if (rows > 0 && cols > 0) {
						html += Array(rows + 1).join(
							'<tr>' +
							Array(cols + 1).join(
								'<td>' + (IE_BR_FIX ? '' : '<br />') + '</td>'
							) +
							'</tr>'
						);

						html += '</table>';

						editor.wysiwygEditorInsertHtml(html);
						editor.closeDropDown(true);
						e.preventDefault();
					}
				});

				editor.createDropDown(caller, 'inserttable', content);
			},
			tooltip: 'Insert a table'
		},
		// END_COMMAND

		// START_COMMAND: Horizontal Rule
		horizontalrule: {
			exec: 'inserthorizontalrule',
			tooltip: 'Insert a horizontal rule'
		},
		// END_COMMAND

		// START_COMMAND: Code
		code: {
			exec: function () {
				this.wysiwygEditorInsertHtml(
					'<code>',
					(IE_BR_FIX ? '' : '<br />') + '</code>'
				);
			},
			tooltip: 'Code'
		},
		// END_COMMAND

		// START_COMMAND: Image
		image: {
			_dropDown: function (editor, caller, selected, cb) {
				var content = createElement('div');

				appendChild(content, _tmpl('image', {
					url: editor._('URL:'),
					width: editor._('Width (optional):'),
					height: editor._('Height (optional):'),
					insert: editor._('Insert')
				}, true));


				var urlInput = find(content, '#image')[0];

				urlInput.value = selected;

				on(content, 'click', '.button', function (e) {
					if (urlInput.value) {
						cb(
							urlInput.value,
							find(content, '#width')[0].value,
							find(content, '#height')[0].value
						);
					}

					editor.closeDropDown(true);
					e.preventDefault();
				});

				editor.createDropDown(caller, 'insertimage', content);
			},
			exec: function (caller) {
				var editor = this;

				defaultCmds.image._dropDown(
					editor,
					caller,
					'',
					function (url, width$$1, height$$1) {
						var attrs = '';

						if (width$$1) {
							attrs += ' width="' + width$$1 + '"';
						}

						if (height$$1) {
							attrs += ' height="' + height$$1 + '"';
						}

						editor.wysiwygEditorInsertHtml(
							'<img' + attrs + ' src="' + url + '" />'
						);
					}
				);
			},
			tooltip: 'Insert an image'
		},
		// END_COMMAND

		// START_COMMAND: E-mail
		email: {
			_dropDown: function (editor, caller, cb) {
				var content = createElement('div');

				appendChild(content, _tmpl('email', {
					label: editor._('E-mail:'),
					desc: editor._('Description (optional):'),
					insert: editor._('Insert')
				}, true));

				on(content, 'click', '.button', function (e) {
					var email = find(content, '#email')[0].value;

					if (email) {
						cb(email, find(content, '#des')[0].value);
					}

					editor.closeDropDown(true);
					e.preventDefault();
				});

				editor.createDropDown(caller, 'insertemail', content);
			},
			exec: function (caller) {
				var editor = this;

				defaultCmds.email._dropDown(
					editor,
					caller,
					function (email, text) {
						// needed for IE to reset the last range
						editor.focus();

						if (!editor.getRangeHelper().selectedHtml() || text) {
							editor.wysiwygEditorInsertHtml(
								'<a href="' + 'mailto:' + email + '">' +
								(text || email) +
								'</a>'
							);
						} else {
							editor.execCommand('createlink', 'mailto:' + email);
						}
					}
				);
			},
			tooltip: 'Insert an email'
		},
		// END_COMMAND

		// START_COMMAND: Link
		link: {
			_dropDown: function (editor, caller, cb) {
				var content = createElement('div');

				appendChild(content, _tmpl('link', {
					url: editor._('URL:'),
					desc: editor._('Description (optional):'),
					ins: editor._('Insert')
				}, true));

				var linkInput = find(content, '#link')[0];

				function insertUrl(e) {
					if (linkInput.value) {
						cb(linkInput.value, find(content, '#des')[0].value);
					}

					editor.closeDropDown(true);
					e.preventDefault();
				}

				on(content, 'click', '.button', insertUrl);
				on(content, 'keypress', function (e) {
					// 13 = enter key
					if (e.which === 13 && linkInput.value) {
						insertUrl(e);
					}
				}, EVENT_CAPTURE);

				editor.createDropDown(caller, 'insertlink', content);
			},
			exec: function (caller) {
				var editor = this;

				defaultCmds.link._dropDown(editor, caller, function (url, text) {
					// needed for IE to restore the last range
					editor.focus();

					// If there is no selected text then must set the URL as
					// the text. Most browsers do this automatically, sadly
					// IE doesn't.
					if (text || !editor.getRangeHelper().selectedHtml()) {
						text = text || url;

						editor.wysiwygEditorInsertHtml(
							'<a href="' + url + '">' + text + '</a>'
						);
					} else {
						editor.execCommand('createlink', url);
					}
				});
			},
			tooltip: 'Insert a link'
		},
		// END_COMMAND

		// START_COMMAND: Unlink
		unlink: {
			state: function () {
				return closest(this.currentNode(), 'a') ? 0 : -1;
			},
			exec: function () {
				var anchor = closest(this.currentNode(), 'a');

				if (anchor) {
					while (anchor.firstChild) {
						insertBefore(anchor.firstChild, anchor);
					}

					remove(anchor);
				}
			},
			tooltip: 'Unlink'
		},
		// END_COMMAND


		// START_COMMAND: Quote
		quote: {
			exec: function (caller, html, author) {
				var before = '<blockquote>',
					end = '</blockquote>';

				// if there is HTML passed set end to null so any selected
				// text is replaced
				if (html) {
					author = (author ? '<cite>' + author + '</cite>' : '');
					before = before + author + html + end;
					end = null;
					// if not add a newline to the end of the inserted quote
				} else if (this.getRangeHelper().selectedHtml() === '') {
					end = (IE_BR_FIX ? '' : '<br />') + end;
				}

				this.wysiwygEditorInsertHtml(before, end);
			},
			tooltip: 'Insert a Quote'
		},
		// END_COMMAND

		// START_COMMAND: Emoticons
		emoticon: {
			exec: function (caller) {
				var editor = this;

				var createContent = function (includeMore) {
					var moreLink,
						opts = editor.opts,
						emoticonsRoot = opts.emoticonsRoot || '',
						emoticonsCompat = opts.emoticonsCompat,
						rangeHelper = editor.getRangeHelper(),
						startSpace = emoticonsCompat &&
							rangeHelper.getOuterText(true, 1) !== ' ' ? ' ' : '',
						endSpace = emoticonsCompat &&
							rangeHelper.getOuterText(false, 1) !== ' ' ? ' ' : '',
						content = createElement('div'),
						line = createElement('div'),
						perLine = 0,
						emoticons = extend(
							{},
							opts.emoticons.dropdown,
							includeMore ? opts.emoticons.more : {}
						);

					appendChild(content, line);

					perLine = Math.sqrt(Object.keys(emoticons).length);

					on(content, 'click', 'img', function (e) {
						editor.insert(startSpace + attr(this, 'alt') + endSpace,
							null, false).closeDropDown(true);

						e.preventDefault();
					});

					each(emoticons, function (code, emoticon) {
						appendChild(line, createElement('img', {
							src: emoticonsRoot + (emoticon.url || emoticon),
							alt: code,
							title: emoticon.tooltip || code
						}));

						if (line.children.length >= perLine) {
							line = createElement('div');
							appendChild(content, line);
						}
					});

					if (!includeMore && opts.emoticons.more) {
						moreLink = createElement('a', {
							className: 'sceditor-more'
						});

						appendChild(moreLink,
							document.createTextNode(editor._('More')));

						on(moreLink, 'click', function (e) {
							editor.createDropDown(
								caller, 'more-emoticons', createContent(true)
							);

							e.preventDefault();
						});

						appendChild(content, moreLink);
					}

					return content;
				};

				editor.createDropDown(caller, 'emoticons', createContent(false));
			},
			txtExec: function (caller) {
				defaultCmds.emoticon.exec.call(this, caller);
			},
			tooltip: 'Insert an emoticon'
		},
		// END_COMMAND

		// START_COMMAND: YouTube
		youtube: {
			_dropDown: function (editor, caller, callback) {
				var content = createElement('div');

				appendChild(content, _tmpl('youtubeMenu', {
					label: editor._('Video URL:'),
					insert: editor._('Insert')
				}, true));

				on(content, 'click', '.button', function (e) {
					var val = find(content, '#link')[0].value;
					var idMatch = val.match(/(?:v=|v\/|embed\/|youtu.be\/)(.{11})/);
					var timeMatch = val.match(/[&|?](?:star)?t=((\d+[hms]?){1,3})/);
					var time = 0;

					if (timeMatch) {
						each(timeMatch[1].split(/[hms]/), function (i, val) {
							if (val !== '') {
								time = (time * 60) + Number(val);
							}
						});
					}

					if (idMatch && /^[a-zA-Z0-9_\-]{11}$/.test(idMatch[1])) {
						callback(idMatch[1], time);
					}

					editor.closeDropDown(true);
					e.preventDefault();
				});

				editor.createDropDown(caller, 'insertlink', content);
			},
			exec: function (btn) {
				var editor = this;

				defaultCmds.youtube._dropDown(editor, btn, function (id, time) {
					editor.wysiwygEditorInsertHtml(_tmpl('youtube', {
						id: id,
						time: time
					}));
				});
			},
			tooltip: 'Insert a YouTube video'
		},
		// END_COMMAND

		// START_COMMAND: Date
		date: {
			_date: function (editor) {
				var now = new Date(),
					year = now.getYear(),
					month = now.getMonth() + 1,
					day = now.getDate();

				if (year < 2000) {
					year = 1900 + year;
				}

				if (month < 10) {
					month = '0' + month;
				}

				if (day < 10) {
					day = '0' + day;
				}

				return editor.opts.dateFormat
					.replace(/year/i, year)
					.replace(/month/i, month)
					.replace(/day/i, day);
			},
			exec: function () {
				this.insertText(defaultCmds.date._date(this));
			},
			txtExec: function () {
				this.insertText(defaultCmds.date._date(this));
			},
			tooltip: 'Insert current date'
		},
		// END_COMMAND

		// START_COMMAND: Time
		time: {
			_time: function () {
				var now = new Date(),
					hours = now.getHours(),
					mins = now.getMinutes(),
					secs = now.getSeconds();

				if (hours < 10) {
					hours = '0' + hours;
				}

				if (mins < 10) {
					mins = '0' + mins;
				}

				if (secs < 10) {
					secs = '0' + secs;
				}

				return hours + ':' + mins + ':' + secs;
			},
			exec: function () {
				this.insertText(defaultCmds.time._time());
			},
			txtExec: function () {
				this.insertText(defaultCmds.time._time());
			},
			tooltip: 'Insert current time'
		},
		// END_COMMAND


		// START_COMMAND: Ltr
		ltr: {
			state: function (parents$$1, firstBlock) {
				return firstBlock && firstBlock.style.direction === 'ltr';
			},
			exec: function () {
				var editor = this,
					rangeHelper = editor.getRangeHelper(),
					node = rangeHelper.getFirstBlockParent();

				editor.focus();

				if (!node || is(node, 'body')) {
					editor.execCommand('formatBlock', 'p');

					node = rangeHelper.getFirstBlockParent();

					if (!node || is(node, 'body')) {
						return;
					}
				}

				var toggleValue = css(node, 'direction') === 'ltr' ? '' : 'ltr';
				css(node, 'direction', toggleValue);
			},
			tooltip: 'Left-to-Right'
		},
		// END_COMMAND

		// START_COMMAND: Rtl
		rtl: {
			state: function (parents$$1, firstBlock) {
				return firstBlock && firstBlock.style.direction === 'rtl';
			},
			exec: function () {
				var editor = this,
					rangeHelper = editor.getRangeHelper(),
					node = rangeHelper.getFirstBlockParent();

				editor.focus();

				if (!node || is(node, 'body')) {
					editor.execCommand('formatBlock', 'p');

					node = rangeHelper.getFirstBlockParent();

					if (!node || is(node, 'body')) {
						return;
					}
				}

				var toggleValue = css(node, 'direction') === 'rtl' ? '' : 'rtl';
				css(node, 'direction', toggleValue);
			},
			tooltip: 'Right-to-Left'
		},
		// END_COMMAND


		// START_COMMAND: Print
		print: {
			exec: 'print',
			tooltip: 'Print'
		},
		// END_COMMAND

		// START_COMMAND: Maximize
		maximize: {
			state: function () {
				return this.maximize();
			},
			exec: function () {
				this.maximize(!this.maximize());
			},
			txtExec: function () {
				this.maximize(!this.maximize());
			},
			tooltip: 'Maximize',
			shortcut: 'Ctrl+Shift+M'
		},
		// END_COMMAND

		// START_COMMAND: Source
		source: {
			state: function () {
				return this.sourceMode();
			},
			exec: function () {
				this.toggleSourceMode();
			},
			txtExec: function () {
				this.toggleSourceMode();
			},
			tooltip: 'View source',
			shortcut: 'Ctrl+Shift+S'
		},
		// END_COMMAND

		// this is here so that commands above can be removed
		// without having to remove the , after the last one.
		// Needed for IE.
		ignore: {}
	};

	var plugins = {};

	/**
	 * Plugin Manager class
	 * @class PluginManager
	 * @name PluginManager
	 */
	function PluginManager(thisObj) {
		/**
		 * Alias of this
		 *
		 * @private
		 * @type {Object}
		 */
		var base = this;

		/**
		 * Array of all currently registered plugins
		 *
		 * @type {Array}
		 * @private
		 */
		var registeredPlugins = [];


		/**
		 * Changes a signals name from "name" into "signalName".
		 *
		 * @param  {string} signal
		 * @return {string}
		 * @private
		 */
		var formatSignalName = function (signal) {
			return 'signal' + signal.charAt(0).toUpperCase() + signal.slice(1);
		};

		/**
		 * Calls handlers for a signal
		 *
		 * @see call()
		 * @see callOnlyFirst()
		 * @param  {Array}   args
		 * @param  {boolean} returnAtFirst
		 * @return {*}
		 * @private
		 */
		var callHandlers = function (args, returnAtFirst) {
			args = [].slice.call(args);

			var idx, ret,
				signal = formatSignalName(args.shift());

			for (idx = 0; idx < registeredPlugins.length; idx++) {
				if (signal in registeredPlugins[idx]) {
					ret = registeredPlugins[idx][signal].apply(thisObj, args);

					if (returnAtFirst) {
						return ret;
					}
				}
			}
		};

		/**
		 * Calls all handlers for the passed signal
		 *
		 * @param  {string}    signal
		 * @param  {...string} args
		 * @function
		 * @name call
		 * @memberOf PluginManager.prototype
		 */
		base.call = function () {
			callHandlers(arguments, false);
		};

		/**
		 * Calls the first handler for a signal, and returns the
		 *
		 * @param  {string}    signal
		 * @param  {...string} args
		 * @return {*} The result of calling the handler
		 * @function
		 * @name callOnlyFirst
		 * @memberOf PluginManager.prototype
		 */
		base.callOnlyFirst = function () {
			return callHandlers(arguments, true);
		};

		/**
		 * Checks if a signal has a handler
		 *
		 * @param  {string} signal
		 * @return {boolean}
		 * @function
		 * @name hasHandler
		 * @memberOf PluginManager.prototype
		 */
		base.hasHandler = function (signal) {
			var i = registeredPlugins.length;
			signal = formatSignalName(signal);

			while (i--) {
				if (signal in registeredPlugins[i]) {
					return true;
				}
			}

			return false;
		};

		/**
		 * Checks if the plugin exists in plugins
		 *
		 * @param  {string} plugin
		 * @return {boolean}
		 * @function
		 * @name exists
		 * @memberOf PluginManager.prototype
		 */
		base.exists = function (plugin) {
			if (plugin in plugins) {
				plugin = plugins[plugin];

				return typeof plugin === 'function' &&
					typeof plugin.prototype === 'object';
			}

			return false;
		};

		/**
		 * Checks if the passed plugin is currently registered.
		 *
		 * @param  {string} plugin
		 * @return {boolean}
		 * @function
		 * @name isRegistered
		 * @memberOf PluginManager.prototype
		 */
		base.isRegistered = function (plugin) {
			if (base.exists(plugin)) {
				var idx = registeredPlugins.length;

				while (idx--) {
					if (registeredPlugins[idx] instanceof plugins[plugin]) {
						return true;
					}
				}
			}

			return false;
		};

		/**
		 * Registers a plugin to receive signals
		 *
		 * @param  {string} plugin
		 * @return {boolean}
		 * @function
		 * @name register
		 * @memberOf PluginManager.prototype
		 */
		base.register = function (plugin) {
			if (!base.exists(plugin) || base.isRegistered(plugin)) {
				return false;
			}

			plugin = new plugins[plugin]();
			registeredPlugins.push(plugin);

			if ('init' in plugin) {
				plugin.init.call(thisObj);
			}

			return true;
		};

		/**
		 * Deregisters a plugin.
		 *
		 * @param  {string} plugin
		 * @return {boolean}
		 * @function
		 * @name deregister
		 * @memberOf PluginManager.prototype
		 */
		base.deregister = function (plugin) {
			var removedPlugin,
				pluginIdx = registeredPlugins.length,
				removed = false;

			if (!base.isRegistered(plugin)) {
				return removed;
			}

			while (pluginIdx--) {
				if (registeredPlugins[pluginIdx] instanceof plugins[plugin]) {
					removedPlugin = registeredPlugins.splice(pluginIdx, 1)[0];
					removed = true;

					if ('destroy' in removedPlugin) {
						removedPlugin.destroy.call(thisObj);
					}
				}
			}

			return removed;
		};

		/**
		 * Clears all plugins and removes the owner reference.
		 *
		 * Calling any functions on this object after calling
		 * destroy will cause a JS error.
		 *
		 * @name destroy
		 * @memberOf PluginManager.prototype
		 */
		base.destroy = function () {
			var i = registeredPlugins.length;

			while (i--) {
				if ('destroy' in registeredPlugins[i]) {
					registeredPlugins[i].destroy.call(thisObj);
				}
			}

			registeredPlugins = [];
			thisObj = null;
		};
	}

	PluginManager.plugins = plugins;

	// In IE < 11 a BR at the end of a block level element
	// causes a line break. In all other browsers it's collapsed.
	var IE_BR_FIX$1 = ie && ie < 11;


	/**
	 * Gets the text, start/end node and offset for
	 * length chars left or right of the passed node
	 * at the specified offset.
	 *
	 * @param  {Node}  node
	 * @param  {number}  offset
	 * @param  {boolean} isLeft
	 * @param  {number}  length
	 * @return {Object}
	 * @private
	 */
	var outerText = function (range, isLeft, length) {
		var nodeValue, remaining, start, end, node,
			text = '',
			next = range.startContainer,
			offset = range.startOffset;

		// Handle cases where node is a paragraph and offset
		// refers to the index of a text node.
		// 3 = text node
		if (next && next.nodeType !== 3) {
			next = next.childNodes[offset];
			offset = 0;
		}

		start = end = offset;

		while (length > text.length && next && next.nodeType === 3) {
			nodeValue = next.nodeValue;
			remaining = length - text.length;

			// If not the first node, start and end should be at their
			// max values as will be updated when getting the text
			if (node) {
				end = nodeValue.length;
				start = 0;
			}

			node = next;

			if (isLeft) {
				start = Math.max(end - remaining, 0);
				offset = start;

				text = nodeValue.substr(start, end - start) + text;
				next = node.previousSibling;
			} else {
				end = Math.min(remaining, nodeValue.length);
				offset = start + end;

				text += nodeValue.substr(start, end);
				next = node.nextSibling;
			}
		}

		return {
			node: node || next,
			offset: offset,
			text: text
		};
	};

	/**
	 * Range helper
	 *
	 * @class RangeHelper
	 * @name RangeHelper
	 */
	function RangeHelper(win, d) {
		var _createMarker, _prepareInput,
			doc = d || win.contentDocument || win.document,
			startMarker = 'sceditor-start-marker',
			endMarker = 'sceditor-end-marker',
			base = this;

		/**
		 * Inserts HTML into the current range replacing any selected
		 * text.
		 *
		 * If endHTML is specified the selected contents will be put between
		 * html and endHTML. If there is nothing selected html and endHTML are
		 * just concatenate together.
		 *
		 * @param {string} html
		 * @param {string} [endHTML]
		 * @return False on fail
		 * @function
		 * @name insertHTML
		 * @memberOf RangeHelper.prototype
		 */
		base.insertHTML = function (html, endHTML) {
			var node, div,
				range = base.selectedRange();

			if (!range) {
				return false;
			}

			if (endHTML) {
				html += base.selectedHtml() + endHTML;
			}

			div = createElement('p', {}, doc);
			node = doc.createDocumentFragment();
			div.innerHTML = html;

			while (div.firstChild) {
				appendChild(node, div.firstChild);
			}

			base.insertNode(node);
		};

		/**
		 * Prepares HTML to be inserted by adding a zero width space
		 * if the last child is empty and adding the range start/end
		 * markers to the last child.
		 *
		 * @param  {Node|string} node
		 * @param  {Node|string} [endNode]
		 * @param  {boolean} [returnHtml]
		 * @return {Node|string}
		 * @private
		 */
		_prepareInput = function (node, endNode, returnHtml) {
			var lastChild,
				frag = doc.createDocumentFragment();

			if (typeof node === 'string') {
				if (endNode) {
					node += base.selectedHtml() + endNode;
				}

				frag = parseHTML(node);
			} else {
				appendChild(frag, node);

				if (endNode) {
					appendChild(frag, base.selectedRange().extractContents());
					appendChild(frag, endNode);
				}
			}

			if (!(lastChild = frag.lastChild)) {
				return;
			}

			while (!isInline(lastChild.lastChild, true)) {
				lastChild = lastChild.lastChild;
			}

			if (canHaveChildren(lastChild)) {
				// Webkit won't allow the cursor to be placed inside an
				// empty tag, so add a zero width space to it.
				if (!lastChild.lastChild) {
					appendChild(lastChild, document.createTextNode('\u200B'));
				}
			} else {
				lastChild = frag;
			}

			base.removeMarkers();

			// Append marks to last child so when restored cursor will be in
			// the right place
			appendChild(lastChild, _createMarker(startMarker));
			appendChild(lastChild, _createMarker(endMarker));

			if (returnHtml) {
				var div = createElement('div');
				appendChild(div, frag);

				return div.innerHTML;
			}

			return frag;
		};

		/**
		 * The same as insertHTML except with DOM nodes instead
		 *
		 * <strong>Warning:</strong> the nodes must belong to the
		 * document they are being inserted into. Some browsers
		 * will throw exceptions if they don't.
		 *
		 * Returns boolean false on fail
		 *
		 * @param {Node} node
		 * @param {Node} endNode
		 * @return {false|undefined}
		 * @function
		 * @name insertNode
		 * @memberOf RangeHelper.prototype
		 */
		base.insertNode = function (node, endNode) {
			var input = _prepareInput(node, endNode),
				range = base.selectedRange(),
				parent$$1 = range.commonAncestorContainer;

			if (!input) {
				return false;
			}

			range.deleteContents();

			// FF allows <br /> to be selected but inserting a node
			// into <br /> will cause it not to be displayed so must
			// insert before the <br /> in FF.
			// 3 = TextNode
			if (parent$$1 && parent$$1.nodeType !== 3 && !canHaveChildren(parent$$1)) {
				insertBefore(input, parent$$1);
			} else {
				range.insertNode(input);
			}

			base.restoreRange();
		};

		/**
		 * Clones the selected Range
		 *
		 * @return {Range}
		 * @function
		 * @name cloneSelected
		 * @memberOf RangeHelper.prototype
		 */
		base.cloneSelected = function () {
			var range = base.selectedRange();

			if (range) {
				return range.cloneRange();
			}
		};

		/**
		 * Gets the selected Range
		 *
		 * @return {Range}
		 * @function
		 * @name selectedRange
		 * @memberOf RangeHelper.prototype
		 */
		base.selectedRange = function () {
			var range, firstChild,
				sel = win.getSelection();

			if (!sel) {
				return;
			}

			// When creating a new range, set the start to the first child
			// element of the body element to avoid errors in FF.
			if (sel.rangeCount <= 0) {
				firstChild = doc.body;
				while (firstChild.firstChild) {
					firstChild = firstChild.firstChild;
				}

				range = doc.createRange();
				// Must be setStartBefore otherwise it can cause infinite
				// loops with lists in WebKit. See issue 442
				range.setStartBefore(firstChild);

				sel.addRange(range);
			}

			if (sel.rangeCount > 0) {
				range = sel.getRangeAt(0);
			}

			return range;
		};

		/**
		 * Gets if there is currently a selection
		 *
		 * @return {boolean}
		 * @function
		 * @name hasSelection
		 * @since 1.4.4
		 * @memberOf RangeHelper.prototype
		 */
		base.hasSelection = function () {
			var sel = win.getSelection();

			return sel && sel.rangeCount > 0;
		};

		/**
		 * Gets the currently selected HTML
		 *
		 * @return {string}
		 * @function
		 * @name selectedHtml
		 * @memberOf RangeHelper.prototype
		 */
		base.selectedHtml = function () {
			var div,
				range = base.selectedRange();

			if (range) {
				div = createElement('p', {}, doc);
				appendChild(div, range.cloneContents());

				return div.innerHTML;
			}

			return '';
		};

		/**
		 * Gets the parent node of the selected contents in the range
		 *
		 * @return {HTMLElement}
		 * @function
		 * @name parentNode
		 * @memberOf RangeHelper.prototype
		 */
		base.parentNode = function () {
			var range = base.selectedRange();

			if (range) {
				return range.commonAncestorContainer;
			}
		};

		/**
		 * Gets the first block level parent of the selected
		 * contents of the range.
		 *
		 * @return {HTMLElement}
		 * @function
		 * @name getFirstBlockParent
		 * @memberOf RangeHelper.prototype
		 */
		/**
		 * Gets the first block level parent of the selected
		 * contents of the range.
		 *
		 * @param {Node} [n] The element to get the first block level parent from
		 * @return {HTMLElement}
		 * @function
		 * @name getFirstBlockParent^2
		 * @since 1.4.1
		 * @memberOf RangeHelper.prototype
		 */
		base.getFirstBlockParent = function (node) {
			var func = function (elm) {
				if (!isInline(elm, true)) {
					return elm;
				}

				elm = elm ? elm.parentNode : null;

				return elm ? func(elm) : elm;
			};

			return func(node || base.parentNode());
		};

		/**
		 * Inserts a node at either the start or end of the current selection
		 *
		 * @param {Bool} start
		 * @param {Node} node
		 * @function
		 * @name insertNodeAt
		 * @memberOf RangeHelper.prototype
		 */
		base.insertNodeAt = function (start, node) {
			var currentRange = base.selectedRange(),
				range = base.cloneSelected();

			if (!range) {
				return false;
			}

			range.collapse(start);
			range.insertNode(node);

			// Reselect the current range.
			// Fixes issue with Chrome losing the selection. Issue#82
			base.selectRange(currentRange);
		};

		/**
		 * Creates a marker node
		 *
		 * @param {string} id
		 * @return {HTMLSpanElement}
		 * @private
		 */
		_createMarker = function (id) {
			base.removeMarker(id);

			var marker = createElement('span', {
				id: id,
				className: 'sceditor-selection sceditor-ignore',
				style: 'display:none;line-height:0'
			}, doc);

			marker.innerHTML = ' ';

			return marker;
		};

		/**
		 * Inserts start/end markers for the current selection
		 * which can be used by restoreRange to re-select the
		 * range.
		 *
		 * @memberOf RangeHelper.prototype
		 * @function
		 * @name insertMarkers
		 */
		base.insertMarkers = function () {
			var currentRange = base.selectedRange();
			var startNode = _createMarker(startMarker);

			base.removeMarkers();
			base.insertNodeAt(true, startNode);

			// Fixes issue with end marker sometimes being placed before
			// the start marker when the range is collapsed.
			if (currentRange && currentRange.collapsed) {
				startNode.parentNode.insertBefore(
					_createMarker(endMarker), startNode.nextSibling);
			} else {
				base.insertNodeAt(false, _createMarker(endMarker));
			}
		};

		/**
		 * Gets the marker with the specified ID
		 *
		 * @param {string} id
		 * @return {Node}
		 * @function
		 * @name getMarker
		 * @memberOf RangeHelper.prototype
		 */
		base.getMarker = function (id) {
			return doc.getElementById(id);
		};

		/**
		 * Removes the marker with the specified ID
		 *
		 * @param {string} id
		 * @function
		 * @name removeMarker
		 * @memberOf RangeHelper.prototype
		 */
		base.removeMarker = function (id) {
			var marker = base.getMarker(id);

			if (marker) {
				remove(marker);
			}
		};

		/**
		 * Removes the start/end markers
		 *
		 * @function
		 * @name removeMarkers
		 * @memberOf RangeHelper.prototype
		 */
		base.removeMarkers = function () {
			base.removeMarker(startMarker);
			base.removeMarker(endMarker);
		};

		/**
		 * Saves the current range location. Alias of insertMarkers()
		 *
		 * @function
		 * @name saveRage
		 * @memberOf RangeHelper.prototype
		 */
		base.saveRange = function () {
			base.insertMarkers();
		};

		/**
		 * Select the specified range
		 *
		 * @param {Range} range
		 * @function
		 * @name selectRange
		 * @memberOf RangeHelper.prototype
		 */
		base.selectRange = function (range) {
			var lastChild;
			var sel = win.getSelection();
			var container = range.endContainer;

			// Check if cursor is set after a BR when the BR is the only
			// child of the parent. In Firefox this causes a line break
			// to occur when something is typed. See issue #321
			if (!IE_BR_FIX$1 && range.collapsed && container &&
				!isInline(container, true)) {

				lastChild = container.lastChild;
				while (lastChild && is(lastChild, '.sceditor-ignore')) {
					lastChild = lastChild.previousSibling;
				}

				if (is(lastChild, 'br')) {
					var rng = doc.createRange();
					rng.setEndAfter(lastChild);
					rng.collapse(false);

					if (base.compare(range, rng)) {
						range.setStartBefore(lastChild);
						range.collapse(true);
					}
				}
			}

			if (sel) {
				base.clear();
				sel.addRange(range);
			}
		};

		/**
		 * Restores the last range saved by saveRange() or insertMarkers()
		 *
		 * @function
		 * @name restoreRange
		 * @memberOf RangeHelper.prototype
		 */
		base.restoreRange = function () {
			var isCollapsed,
				range = base.selectedRange(),
				start = base.getMarker(startMarker),
				end = base.getMarker(endMarker);

			if (!start || !end || !range) {
				return false;
			}

			isCollapsed = start.nextSibling === end;

			range = doc.createRange();
			range.setStartBefore(start);
			range.setEndAfter(end);

			if (isCollapsed) {
				range.collapse(true);
			}

			base.selectRange(range);
			base.removeMarkers();
		};

		/**
		 * Selects the text left and right of the current selection
		 *
		 * @param {number} left
		 * @param {number} right
		 * @since 1.4.3
		 * @function
		 * @name selectOuterText
		 * @memberOf RangeHelper.prototype
		 */
		base.selectOuterText = function (left, right) {
			var start, end,
				range = base.cloneSelected();

			if (!range) {
				return false;
			}

			range.collapse(false);

			start = outerText(range, true, left);
			end = outerText(range, false, right);

			range.setStart(start.node, start.offset);
			range.setEnd(end.node, end.offset);

			base.selectRange(range);
		};

		/**
		 * Gets the text left or right of the current selection
		 *
		 * @param {boolean} before
		 * @param {number} length
		 * @return {string}
		 * @since 1.4.3
		 * @function
		 * @name selectOuterText
		 * @memberOf RangeHelper.prototype
		 */
		base.getOuterText = function (before, length) {
			var range = base.cloneSelected();

			if (!range) {
				return '';
			}

			range.collapse(!before);

			return outerText(range, before, length).text;
		};

		/**
		 * Replaces keywords with values based on the current caret position
		 *
		 * @param {Array}   keywords
		 * @param {boolean} includeAfter      If to include the text after the
		 *                                    current caret position or just
		 *                                    text before
		 * @param {boolean} keywordsSorted    If the keywords array is pre
		 *                                    sorted shortest to longest
		 * @param {number}  longestKeyword    Length of the longest keyword
		 * @param {boolean} requireWhitespace If the key must be surrounded
		 *                                    by whitespace
		 * @param {string}  keypressChar      If this is being called from
		 *                                    a keypress event, this should be
		 *                                    set to the pressed character
		 * @return {boolean}
		 * @function
		 * @name replaceKeyword
		 * @memberOf RangeHelper.prototype
		 */
		// eslint-disable-next-line max-params
		base.replaceKeyword = function (
			keywords,
			includeAfter,
			keywordsSorted,
			longestKeyword,
			requireWhitespace,
			keypressChar
		) {
			if (!keywordsSorted) {
				keywords.sort(function (a, b) {
					return a[0].length - b[0].length;
				});
			}

			var outerText, match, matchPos, startIndex,
				leftLen, charsLeft, keyword, keywordLen,
				whitespaceRegex = '(^|[\\s\xA0\u2002\u2003\u2009])',
				keywordIdx = keywords.length,
				whitespaceLen = requireWhitespace ? 1 : 0,
				maxKeyLen = longestKeyword ||
					keywords[keywordIdx - 1][0].length;

			if (requireWhitespace) {
				maxKeyLen++;
			}

			keypressChar = keypressChar || '';
			outerText = base.getOuterText(true, maxKeyLen);
			leftLen = outerText.length;
			outerText += keypressChar;

			if (includeAfter) {
				outerText += base.getOuterText(false, maxKeyLen);
			}

			while (keywordIdx--) {
				keyword = keywords[keywordIdx][0];
				keywordLen = keyword.length;
				startIndex = Math.max(0, leftLen - keywordLen - whitespaceLen);
				matchPos = -1;

				if (requireWhitespace) {
					match = outerText
						.substr(startIndex)
						.match(new RegExp(whitespaceRegex +
							regex(keyword) + whitespaceRegex));

					if (match) {
						// Add the length of the text that was removed by
						// substr() and also add 1 for the whitespace
						matchPos = match.index + startIndex + match[1].length;
					}
				} else {
					matchPos = outerText.indexOf(keyword, startIndex);
				}

				if (matchPos > -1) {
					// Make sure the match is between before and
					// after, not just entirely in one side or the other
					if (matchPos <= leftLen &&
						matchPos + keywordLen + whitespaceLen >= leftLen) {
						charsLeft = leftLen - matchPos;

						// If the keypress char is white space then it should
						// not be replaced, only chars that are part of the
						// key should be replaced.
						base.selectOuterText(
							charsLeft,
							keywordLen - charsLeft -
							(/^\S/.test(keypressChar) ? 1 : 0)
						);

						base.insertHTML(keywords[keywordIdx][1]);
						return true;
					}
				}
			}

			return false;
		};

		/**
		 * Compares two ranges.
		 *
		 * If rangeB is undefined it will be set to
		 * the current selected range
		 *
		 * @param  {Range} rngA
		 * @param  {Range} [rngB]
		 * @return {boolean}
		 * @function
		 * @name compare
		 * @memberOf RangeHelper.prototype
		 */
		base.compare = function (rngA, rngB) {
			if (!rngB) {
				rngB = base.selectedRange();
			}

			if (!rngA || !rngB) {
				return !rngA && !rngB;
			}

			return rngA.compareBoundaryPoints(Range.END_TO_END, rngB) === 0 &&
				rngA.compareBoundaryPoints(Range.START_TO_START, rngB) === 0;
		};

		/**
		 * Removes any current selection
		 *
		 * @since 1.4.6
		 * @function
		 * @name clear
		 * @memberOf RangeHelper.prototype
		 */
		base.clear = function () {
			var sel = win.getSelection();

			if (sel) {
				if (sel.removeAllRanges) {
					sel.removeAllRanges();
				} else if (sel.empty) {
					sel.empty();
				}
			}
		};
	}

	/**
	 * Checks all emoticons are surrounded by whitespace and
	 * replaces any that aren't with with their emoticon code.
	 *
	 * @param {HTMLElement} node
	 * @param {rangeHelper} rangeHelper
	 * @return {void}
	 */
	function checkWhitespace(node, rangeHelper) {
		var noneWsRegex = /[^\s\xA0\u2002\u2003\u2009\u00a0]+/;
		var emoticons = node && find(node, 'img[data-sceditor-emoticon]');

		if (!node || !emoticons.length) {
			return;
		}

		for (var i = 0; i < emoticons.length; i++) {
			var emoticon = emoticons[i];
			var parent$$1 = emoticon.parentNode;
			var prev = emoticon.previousSibling;
			var next = emoticon.nextSibling;

			if ((!prev || !noneWsRegex.test(prev.nodeValue.slice(-1))) &&
				(!next || !noneWsRegex.test((next.nodeValue || '')[0]))) {
				continue;
			}

			var range = rangeHelper.cloneSelected();
			var rangeStart = -1;
			var rangeStartContainer = range.startContainer;
			var previousText = prev.nodeValue;

			// For IE's HTMLPhraseElement
			if (previousText === null) {
				previousText = prev.innerText || '';
			}

			previousText += data(emoticon, 'sceditor-emoticon');

			// If the cursor is after the removed emoticon, add
			// the length of the newly added text to it
			if (rangeStartContainer === next) {
				rangeStart = previousText.length + range.startOffset;
			}

			// If the cursor is set before the next node, set it to
			// the end of the new text node
			if (rangeStartContainer === node &&
				node.childNodes[range.startOffset] === next) {
				rangeStart = previousText.length;
			}

			// If the cursor is set before the removed emoticon,
			// just keep it at that position
			if (rangeStartContainer === prev) {
				rangeStart = range.startOffset;
			}

			if (!next || next.nodeType !== TEXT_NODE) {
				next = parent$$1.insertBefore(
					parent$$1.ownerDocument.createTextNode(''), next
				);
			}

			next.insertData(0, previousText);
			remove(prev);
			remove(emoticon);

			// Need to update the range starting position if it's been modified
			if (rangeStart > -1) {
				range.setStart(next, rangeStart);
				range.collapse(true);
				rangeHelper.selectRange(range);
			}
		}
	}

	/**
	 * Replaces any emoticons inside the root node with images.
	 *
	 * emoticons should be an object where the key is the emoticon
	 * code and the value is the HTML to replace it with.
	 *
	 * @param {HTMLElement} root
	 * @param {Object<string, string>} emoticons
	 * @param {boolean} emoticonsCompat
	 * @return {void}
	 */
	function replace(root, emoticons, emoticonsCompat) {
		var doc = root.ownerDocument;
		var space = '(^|\\s|\xA0|\u2002|\u2003|\u2009|$)';
		var emoticonCodes = [];
		var emoticonRegex = {};

		// TODO: Make this tag configurable.
		if (parent(root, 'code')) {
			return;
		}

		each(emoticons, function (key) {
			emoticonRegex[key] = new RegExp(space + regex(key) + space);
			emoticonCodes.push(key);
		});

		// Sort keys longest to shortest so that longer keys
		// take precedence (avoids bugs with shorter keys partially
		// matching longer ones)
		emoticonCodes.sort(function (a, b) {
			return b.length - a.length;
		});

		(function convert(node) {
			node = node.firstChild;

			while (node) {
				// TODO: Make this tag configurable.
				if (node.nodeType === ELEMENT_NODE && !is(node, 'code')) {
					convert(node);
				}

				if (node.nodeType === TEXT_NODE) {
					for (var i = 0; i < emoticonCodes.length; i++) {
						var text = node.nodeValue;
						var key = emoticonCodes[i];
						var index = emoticonsCompat ?
							text.search(emoticonRegex[key]) :
							text.indexOf(key);

						if (index > -1) {
							// When emoticonsCompat is enabled this will be the
							// position after any white space
							var startIndex = text.indexOf(key, index);
							var fragment = parseHTML(emoticons[key], doc);
							var after = text.substr(startIndex + key.length);

							fragment.appendChild(doc.createTextNode(after));

							node.nodeValue = text.substr(0, startIndex);
							node.parentNode
								.insertBefore(fragment, node.nextSibling);
						}
					}
				}

				node = node.nextSibling;
			}
		}(root));
	}

	var globalWin = window;
	var globalDoc = document;

	var IE_VER = ie;

	// In IE < 11 a BR at the end of a block level element
	// causes a line break. In all other browsers it's collapsed.
	var IE_BR_FIX$2 = IE_VER && IE_VER < 11;

	var IMAGE_MIME_REGEX = /^image\/(p?jpe?g|gif|png|bmp)$/i;

	/**
	 * Wrap inlines that are in the root in paragraphs.
	 *
	 * @param {HTMLBodyElement} body
	 * @param {Document} doc
	 * @private
	 */
	function wrapInlines(body, doc) {
		var wrapper;

		traverse(body, function (node) {
			if (isInline(node, true)) {
				if (!wrapper) {
					wrapper = createElement('p', {}, doc);
					insertBefore(wrapper, node);
				}

				if (node.nodeType !== TEXT_NODE || node.nodeValue !== '') {
					appendChild(wrapper, node);
				}
			} else {
				wrapper = null;
			}
		}, false, true);
	}

	/**
	 * SCEditor - A lightweight WYSIWYG editor
	 *
	 * @param {HTMLTextAreaElement} original The textarea to be converted
	 * @param {Object} userOptions
	 * @class SCEditor
	 * @name SCEditor
	 */
	function SCEditor(original, userOptions) {
		/**
		 * Alias of this
		 *
		 * @private
		 */
		var base = this;

		/**
		 * Editor format like BBCode or HTML
		 */
		var format;

		/**
		 * The div which contains the editor and toolbar
		 *
		 * @type {HTMLDivElement}
		 * @private
		 */
		var editorContainer;

		/**
		 * Map of events handlers bound to this instance.
		 *
		 * @type {Object}
		 * @private
		 */
		var eventHandlers = {};

		/**
		 * The editors toolbar
		 *
		 * @type {HTMLDivElement}
		 * @private
		 */
		var toolbar;

		/**
		 * The editors iframe which should be in design mode
		 *
		 * @type {HTMLIFrameElement}
		 * @private
		 */
		var wysiwygEditor;

		/**
		 * The editors window
		 *
		 * @type {Window}
		 * @private
		 */
		var wysiwygWindow;

		/**
		 * The WYSIWYG editors body element
		 *
		 * @type {HTMLBodyElement}
		 * @private
		 */
		var wysiwygBody;

		/**
		 * The WYSIWYG editors document
		 *
		 * @type {Document}
		 * @private
		 */
		var wysiwygDocument;

		/**
		 * The editors textarea for viewing source
		 *
		 * @type {HTMLTextAreaElement}
		 * @private
		 */
		var sourceEditor;

		/**
		 * The current dropdown
		 *
		 * @type {HTMLDivElement}
		 * @private
		 */
		var dropdown;

		/**
		 * Store the last cursor position. Needed for IE because it forgets
		 *
		 * @type {Range}
		 * @private
		 */
		var lastRange;

		/**
		 * If the user is currently composing text via IME
		 * @type {boolean}
		 */
		var isComposing;

		/**
		 * Timer for valueChanged key handler
		 * @type {number}
		 */
		var valueChangedKeyUpTimer;

		/**
		 * The editors locale
		 *
		 * @private
		 */
		var locale;

		/**
		 * Stores a cache of preloaded images
		 *
		 * @private
		 * @type {Array.<HTMLImageElement>}
		 */
		var preLoadCache = [];

		/**
		 * The editors rangeHelper instance
		 *
		 * @type {RangeHelper}
		 * @private
		 */
		var rangeHelper;

		/**
		 * An array of button state handlers
		 *
		 * @type {Array.<Object>}
		 * @private
		 */
		var btnStateHandlers = [];

		/**
		 * Plugin manager instance
		 *
		 * @type {PluginManager}
		 * @private
		 */
		var pluginManager;

		/**
		 * The current node containing the selection/caret
		 *
		 * @type {Node}
		 * @private
		 */
		var currentNode;

		/**
		 * The first block level parent of the current node
		 *
		 * @type {node}
		 * @private
		 */
		var currentBlockNode;

		/**
		 * The current node selection/caret
		 *
		 * @type {Object}
		 * @private
		 */
		var currentSelection;

		/**
		 * Used to make sure only 1 selection changed
		 * check is called every 100ms.
		 *
		 * Helps improve performance as it is checked a lot.
		 *
		 * @type {boolean}
		 * @private
		 */
		var isSelectionCheckPending;

		/**
		 * If content is required (equivalent to the HTML5 required attribute)
		 *
		 * @type {boolean}
		 * @private
		 */
		var isRequired;

		/**
		 * The inline CSS style element. Will be undefined
		 * until css() is called for the first time.
		 *
		 * @type {HTMLStyleElement}
		 * @private
		 */
		var inlineCss;

		/**
		 * Object containing a list of shortcut handlers
		 *
		 * @type {Object}
		 * @private
		 */
		var shortcutHandlers = {};

		/**
		 * The min and max heights that autoExpand should stay within
		 *
		 * @type {Object}
		 * @private
		 */
		var autoExpandBounds;

		/**
		 * Timeout for the autoExpand function to throttle calls
		 *
		 * @private
		 */
		var autoExpandThrottle;

		/**
		 * Cache of the current toolbar buttons
		 *
		 * @type {Object}
		 * @private
		 */
		var toolbarButtons = {};

		/**
		 * Last scroll position before maximizing so
		 * it can be restored when finished.
		 *
		 * @type {number}
		 * @private
		 */
		var maximizeScrollPosition;

		/**
		 * Stores the contents while a paste is taking place.
		 *
		 * Needed to support browsers that lack clipboard API support.
		 *
		 * @type {?DocumentFragment}
		 * @private
		 */
		var pasteContentFragment;

		/**
		 * All the emoticons from dropdown, more and hidden combined
		 * and with the emoticons root set
		 *
		 * @type {!Object<string, string>}
		 * @private
		 */
		var allEmoticons = {};

		/**
		 * Current icon set if any
		 *
		 * @type {?Object}
		 * @private
		 */
		var icons;

		/**
		 * Private functions
		 * @private
		 */
		var init,
			replaceEmoticons,
			handleCommand,
			saveRange,
			initEditor,
			initPlugins,
			initLocale,
			initToolBar,
			initOptions,
			initEvents,
			initResize,
			initEmoticons,
			handlePasteEvt,
			handlePasteData,
			handleKeyDown,
			handleBackSpace,
			handleKeyPress,
			handleFormReset,
			handleMouseDown,
			handleComposition,
			handleEvent,
			handleDocumentClick,
			updateToolBar,
			updateActiveButtons,
			sourceEditorSelectedText,
			appendNewLine,
			checkSelectionChanged,
			checkNodeChanged,
			autofocus,
			emoticonsKeyPress,
			emoticonsCheckWhitespace,
			currentStyledBlockNode,
			triggerValueChanged,
			valueChangedBlur,
			valueChangedKeyUp,
			autoUpdate,
			autoExpand;

		/**
		 * All the commands supported by the editor
		 * @name commands
		 * @memberOf SCEditor.prototype
		 */
		base.commands = extend(true, {}, (userOptions.commands || defaultCmds));

		/**
		 * Options for this editor instance
		 * @name opts
		 * @memberOf SCEditor.prototype
		 */
		var options = base.opts = extend(
			true, {}, defaultOptions, userOptions
		);

		// Don't deep extend emoticons (fixes #565)
		base.opts.emoticons = userOptions.emoticons || defaultOptions.emoticons;

		/**
		 * Creates the editor iframe and textarea
		 * @private
		 */
		init = function () {
			original._sceditor = base;

			// Load locale
			if (options.locale && options.locale !== 'en') {
				initLocale();
			}

			editorContainer = createElement('div', {
				className: 'sceditor-container'
			});

			insertBefore(editorContainer, original);
			css(editorContainer, 'z-index', options.zIndex);

			// Add IE version to the container to allow IE specific CSS
			// fixes without using CSS hacks or conditional comments
			if (IE_VER) {
				addClass(editorContainer, 'ie ie' + IE_VER);
			}

			isRequired = original.required;
			original.required = false;

			var FormatCtor = SCEditor.formats[options.format];
			format = FormatCtor ? new FormatCtor() : {};
			if ('init' in format) {
				format.init.call(base);
			}

			// create the editor
			initPlugins();
			initEmoticons();
			initToolBar();
			initEditor();
			initOptions();
			initEvents();

			// force into source mode if is a browser that can't handle
			// full editing
			if (!isWysiwygSupported) {
				base.toggleSourceMode();
			}

			updateActiveButtons();

			var loaded = function () {
				off(globalWin, 'load', loaded);

				if (options.autofocus) {
					autofocus();
				}

				autoExpand();
				appendNewLine();
				// TODO: use editor doc and window?
				pluginManager.call('ready');
				if ('onReady' in format) {
					format.onReady.call(base);
				}
			};
			on(globalWin, 'load', loaded);
			if (globalDoc.readyState === 'complete') {
				loaded();
			}
		};

		initPlugins = function () {
			var plugins = options.plugins;

			plugins = plugins ? plugins.toString().split(',') : [];
			pluginManager = new PluginManager(base);

			plugins.forEach(function (plugin) {
				pluginManager.register(plugin.trim());
			});
		};

		/**
		 * Init the locale variable with the specified locale if possible
		 * @private
		 * @return void
		 */
		initLocale = function () {
			var lang;

			locale = SCEditor.locale[options.locale];

			if (!locale) {
				lang = options.locale.split('-');
				locale = SCEditor.locale[lang[0]];
			}

			// Locale DateTime format overrides any specified in the options
			if (locale && locale.dateFormat) {
				options.dateFormat = locale.dateFormat;
			}
		};

		/**
		 * Creates the editor iframe and textarea
		 * @private
		 */
		initEditor = function () {
			sourceEditor = createElement('textarea');
			wysiwygEditor = createElement('iframe', {
				frameborder: 0,
				allowfullscreen: true
			});

			/* This needs to be done right after they are created because,
			 * for any reason, the user may not want the value to be tinkered
			 * by any filters.
			 */
			if (options.startInSourceMode) {
				addClass(editorContainer, 'sourceMode');
				hide(wysiwygEditor);
			} else {
				addClass(editorContainer, 'wysiwygMode');
				hide(sourceEditor);
			}

			if (!options.spellcheck) {
				attr(editorContainer, 'spellcheck', 'false');
			}

			if (globalWin.location.protocol === 'https:') {
				// eslint-disable-next-line no-script-url
				attr(wysiwygEditor, 'src', 'javascript:false');
			}

			// Add the editor to the container
			appendChild(editorContainer, wysiwygEditor);
			appendChild(editorContainer, sourceEditor);

			// TODO: make this optional somehow
			base.dimensions(
				options.width || width(original),
				options.height || height(original)
			);

			// Add IE version class to the HTML element so can apply
			// conditional styling without CSS hacks
			var className = IE_VER ? 'ie ie' + IE_VER : '';
			// Add ios to HTML so can apply CSS fix to only it
			className += ios ? ' ios' : '';

			wysiwygDocument = wysiwygEditor.contentDocument;
			wysiwygDocument.open();
			wysiwygDocument.write(_tmpl('html', {
				attrs: ' class="' + className + '"',
				spellcheck: options.spellcheck ? '' : 'spellcheck="false"',
				charset: options.charset,
				style: options.style
			}));
			wysiwygDocument.close();

			wysiwygBody = wysiwygDocument.body;
			wysiwygWindow = wysiwygEditor.contentWindow;

			base.readOnly(!!options.readOnly);

			// iframe overflow fix for iOS, also fixes an IE issue with the
			// editor not getting focus when clicking inside
			if (ios || edge || IE_VER) {
				height(wysiwygBody, '100%');

				if (!IE_VER) {
					on(wysiwygBody, 'touchend', base.focus);
				}
			}

			var tabIndex = attr(original, 'tabindex');
			attr(sourceEditor, 'tabindex', tabIndex);
			attr(wysiwygEditor, 'tabindex', tabIndex);

			rangeHelper = new RangeHelper(wysiwygWindow);

			// load any textarea value into the editor
			hide(original);
			base.val(original.value);

			var placeholder = options.placeholder ||
				attr(original, 'placeholder');

			if (placeholder) {
				sourceEditor.placeholder = placeholder;
				attr(wysiwygBody, 'placeholder', placeholder);
			}
		};

		/**
		 * Initialises options
		 * @private
		 */
		initOptions = function () {
			// auto-update original textbox on blur if option set to true
			if (options.autoUpdate) {
				on(wysiwygBody, 'blur', autoUpdate);
				on(sourceEditor, 'blur', autoUpdate);
			}

			if (options.rtl === null) {
				options.rtl = css(sourceEditor, 'direction') === 'rtl';
			}

			base.rtl(!!options.rtl);

			if (options.autoExpand) {
				// Need to update when images (or anything else) loads
				on(wysiwygBody, 'load', autoExpand, EVENT_CAPTURE);
				on(wysiwygBody, 'input keyup', autoExpand);
			}

			if (options.resizeEnabled) {
				initResize();
			}

			attr(editorContainer, 'id', options.id);
			base.emoticons(options.emoticonsEnabled);
		};

		/**
		 * Initialises events
		 * @private
		 */
		initEvents = function () {
			var form = original.form;
			var compositionEvents = 'compositionstart compositionend';
			var eventsToForward = 'keydown keyup keypress focus blur contextmenu';
			var checkSelectionEvents = 'onselectionchange' in wysiwygDocument ?
				'selectionchange' :
				'keyup focus blur contextmenu mouseup touchend click';

			on(globalDoc, 'click', handleDocumentClick);

			if (form) {
				on(form, 'reset', handleFormReset);
				on(form, 'submit', base.updateOriginal, EVENT_CAPTURE);
			}

			on(wysiwygBody, 'keypress', handleKeyPress);
			on(wysiwygBody, 'keydown', handleKeyDown);
			on(wysiwygBody, 'keydown', handleBackSpace);
			on(wysiwygBody, 'keyup', appendNewLine);
			on(wysiwygBody, 'blur', valueChangedBlur);
			on(wysiwygBody, 'keyup', valueChangedKeyUp);
			on(wysiwygBody, 'paste', handlePasteEvt);
			on(wysiwygBody, compositionEvents, handleComposition);
			on(wysiwygBody, checkSelectionEvents, checkSelectionChanged);
			on(wysiwygBody, eventsToForward, handleEvent);

			if (options.emoticonsCompat && globalWin.getSelection) {
				on(wysiwygBody, 'keyup', emoticonsCheckWhitespace);
			}

			on(wysiwygBody, 'blur', function () {
				if (!base.val()) {
					addClass(wysiwygBody, 'placeholder');
				}
			});

			on(wysiwygBody, 'focus', function () {
				removeClass(wysiwygBody, 'placeholder');
			});

			on(sourceEditor, 'blur', valueChangedBlur);
			on(sourceEditor, 'keyup', valueChangedKeyUp);
			on(sourceEditor, 'keydown', handleKeyDown);
			on(sourceEditor, compositionEvents, handleComposition);
			on(sourceEditor, eventsToForward, handleEvent);

			on(wysiwygDocument, 'mousedown', handleMouseDown);
			on(wysiwygDocument, checkSelectionEvents, checkSelectionChanged);
			on(wysiwygDocument, 'beforedeactivate keyup mouseup', saveRange);
			on(wysiwygDocument, 'keyup', appendNewLine);
			on(wysiwygDocument, 'focus', function () {
				lastRange = null;
			});

			on(editorContainer, 'selectionchanged', checkNodeChanged);
			on(editorContainer, 'selectionchanged', updateActiveButtons);
			// Custom events to forward
			on(
				editorContainer,
				'selectionchanged valuechanged nodechanged pasteraw paste',
				handleEvent
			);
		};

		/**
		 * Creates the toolbar and appends it to the container
		 * @private
		 */
		initToolBar = function () {
			var group,
				commands = base.commands,
				exclude = (options.toolbarExclude || '').split(','),
				groups = options.toolbar.split('|');

			toolbar = createElement('div', {
				className: 'sceditor-toolbar',
				unselectable: 'on'
			});

			if (options.icons in SCEditor.icons) {
				icons = new SCEditor.icons[options.icons]();
			}

			each(groups, function (_, menuItems) {
				group = createElement('div', {
					className: 'sceditor-group'
				});

				each(menuItems.split(','), function (_, commandName) {
					var button, shortcut,
						command = commands[commandName];

					// The commandName must be a valid command and not excluded
					if (!command || exclude.indexOf(commandName) > -1) {
						return;
					}

					shortcut = command.shortcut;
					button = _tmpl('toolbarButton', {
						name: commandName,
						dispName: base._(command.name ||
							command.tooltip || commandName)
					}, true).firstChild;

					if (icons && icons.create) {
						var icon = icons.create(commandName);
						if (icon) {
							insertBefore(icons.create(commandName),
								button.firstChild);
							addClass(button, 'has-icon');
						}
					}

					button._sceTxtMode = !!command.txtExec;
					button._sceWysiwygMode = !!command.exec;
					toggleClass(button, 'disabled', !command.exec);
					on(button, 'click', function (e) {
						if (!hasClass(button, 'disabled')) {
							handleCommand(button, command);
						}

						updateActiveButtons();
						e.preventDefault();
					});
					// Prevent editor losing focus when button clicked
					on(button, 'mousedown', function (e) {
						base.closeDropDown();
						e.preventDefault();
					});

					if (command.tooltip) {
						attr(button, 'title',
							base._(command.tooltip) +
							(shortcut ? ' (' + shortcut + ')' : '')
						);
					}

					if (shortcut) {
						base.addShortcut(shortcut, commandName);
					}

					if (command.state) {
						btnStateHandlers.push({
							name: commandName,
							state: command.state
						});
						// exec string commands can be passed to queryCommandState
					} else if (isString(command.exec)) {
						btnStateHandlers.push({
							name: commandName,
							state: command.exec
						});
					}

					appendChild(group, button);
					toolbarButtons[commandName] = button;
				});

				// Exclude empty groups
				if (group.firstChild) {
					appendChild(toolbar, group);
				}
			});

			// Append the toolbar to the toolbarContainer option if given
			appendChild(options.toolbarContainer || editorContainer, toolbar);
		};

		/**
		 * Creates the resizer.
		 * @private
		 */
		initResize = function () {
			var minHeight, maxHeight, minWidth, maxWidth,
				mouseMoveFunc, mouseUpFunc,
				grip = createElement('div', {
					className: 'sceditor-grip'
				}),
				// Cover is used to cover the editor iframe so document
				// still gets mouse move events
				cover = createElement('div', {
					className: 'sceditor-resize-cover'
				}),
				moveEvents = 'touchmove mousemove',
				endEvents = 'touchcancel touchend mouseup',
				startX = 0,
				startY = 0,
				newX = 0,
				newY = 0,
				startWidth = 0,
				startHeight = 0,
				origWidth = width(editorContainer),
				origHeight = height(editorContainer),
				isDragging = false,
				rtl = base.rtl();

			minHeight = options.resizeMinHeight || origHeight / 1.5;
			maxHeight = options.resizeMaxHeight || origHeight * 2.5;
			minWidth = options.resizeMinWidth || origWidth / 1.25;
			maxWidth = options.resizeMaxWidth || origWidth * 1.25;

			mouseMoveFunc = function (e) {
				// iOS uses window.event
				if (e.type === 'touchmove') {
					e = globalWin.event;
					newX = e.changedTouches[0].pageX;
					newY = e.changedTouches[0].pageY;
				} else {
					newX = e.pageX;
					newY = e.pageY;
				}

				var newHeight = startHeight + (newY - startY),
					newWidth = rtl ?
						startWidth - (newX - startX) :
						startWidth + (newX - startX);

				if (maxWidth > 0 && newWidth > maxWidth) {
					newWidth = maxWidth;
				}
				if (minWidth > 0 && newWidth < minWidth) {
					newWidth = minWidth;
				}
				if (!options.resizeWidth) {
					newWidth = false;
				}

				if (maxHeight > 0 && newHeight > maxHeight) {
					newHeight = maxHeight;
				}
				if (minHeight > 0 && newHeight < minHeight) {
					newHeight = minHeight;
				}
				if (!options.resizeHeight) {
					newHeight = false;
				}

				if (newWidth || newHeight) {
					base.dimensions(newWidth, newHeight);
				}

				e.preventDefault();
			};

			mouseUpFunc = function (e) {
				if (!isDragging) {
					return;
				}

				isDragging = false;

				hide(cover);
				removeClass(editorContainer, 'resizing');
				off(globalDoc, moveEvents, mouseMoveFunc);
				off(globalDoc, endEvents, mouseUpFunc);

				e.preventDefault();
			};

			if (icons && icons.create) {
				var icon = icons.create('grip');
				if (icon) {
					appendChild(grip, icon);
					addClass(grip, 'has-icon');
				}
			}

			appendChild(editorContainer, grip);
			appendChild(editorContainer, cover);
			hide(cover);

			on(grip, 'touchstart mousedown', function (e) {
				// iOS uses window.event
				if (e.type === 'touchstart') {
					e = globalWin.event;
					startX = e.touches[0].pageX;
					startY = e.touches[0].pageY;
				} else {
					startX = e.pageX;
					startY = e.pageY;
				}

				startWidth = width(editorContainer);
				startHeight = height(editorContainer);
				isDragging = true;

				addClass(editorContainer, 'resizing');
				show(cover);
				on(globalDoc, moveEvents, mouseMoveFunc);
				on(globalDoc, endEvents, mouseUpFunc);

				e.preventDefault();
			});
		};

		/**
		 * Prefixes and preloads the emoticon images
		 * @private
		 */
		initEmoticons = function () {
			var emoticons = options.emoticons;
			var root = options.emoticonsRoot || '';

			if (emoticons) {
				allEmoticons = extend(
					{}, emoticons.more, emoticons.dropdown, emoticons.hidden
				);
			}

			each(allEmoticons, function (key, url) {
				allEmoticons[key] = _tmpl('emoticon', {
					key: key,
					// Prefix emoticon root to emoticon urls
					url: root + (url.url || url),
					tooltip: url.tooltip || key
				});

				// Preload the emoticon
				if (options.emoticonsEnabled) {
					preLoadCache.push(createElement('img', {
						src: root + (url.url || url)
					}));
				}
			});
		};

		/**
		 * Autofocus the editor
		 * @private
		 */
		autofocus = function () {
			var range, txtPos,
				node = wysiwygBody.firstChild,
				focusEnd = !!options.autofocusEnd;

			// Can't focus invisible elements
			if (!isVisible(editorContainer)) {
				return;
			}

			if (base.sourceMode()) {
				txtPos = focusEnd ? sourceEditor.value.length : 0;

				sourceEditor.setSelectionRange(txtPos, txtPos);

				return;
			}

			removeWhiteSpace(wysiwygBody);

			if (focusEnd) {
				if (!(node = wysiwygBody.lastChild)) {
					node = createElement('p', {}, wysiwygDocument);
					appendChild(wysiwygBody, node);
				}

				while (node.lastChild) {
					node = node.lastChild;

					// IE < 11 should place the cursor after the <br> as
					// it will show it as a newline. IE >= 11 and all
					// other browsers should place the cursor before.
					if (!IE_BR_FIX$2 && is(node, 'br') && node.previousSibling) {
						node = node.previousSibling;
					}
				}
			}

			range = wysiwygDocument.createRange();

			if (!canHaveChildren(node)) {
				range.setStartBefore(node);

				if (focusEnd) {
					range.setStartAfter(node);
				}
			} else {
				range.selectNodeContents(node);
			}

			range.collapse(!focusEnd);
			rangeHelper.selectRange(range);
			currentSelection = range;

			if (focusEnd) {
				wysiwygBody.scrollTop = wysiwygBody.scrollHeight;
			}

			base.focus();
		};

		/**
		 * Gets if the editor is read only
		 *
		 * @since 1.3.5
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name readOnly
		 * @return {boolean}
		 */
		/**
		 * Sets if the editor is read only
		 *
		 * @param {boolean} readOnly
		 * @since 1.3.5
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name readOnly^2
		 * @return {this}
		 */
		base.readOnly = function (readOnly) {
			if (typeof readOnly !== 'boolean') {
				return !sourceEditor.readonly;
			}

			wysiwygBody.contentEditable = !readOnly;
			sourceEditor.readonly = !readOnly;

			updateToolBar(readOnly);

			return base;
		};

		/**
		 * Gets if the editor is in RTL mode
		 *
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name rtl
		 * @return {boolean}
		 */
		/**
		 * Sets if the editor is in RTL mode
		 *
		 * @param {boolean} rtl
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name rtl^2
		 * @return {this}
		 */
		base.rtl = function (rtl) {
			var dir = rtl ? 'rtl' : 'ltr';

			if (typeof rtl !== 'boolean') {
				return attr(sourceEditor, 'dir') === 'rtl';
			}

			attr(wysiwygBody, 'dir', dir);
			attr(sourceEditor, 'dir', dir);

			removeClass(editorContainer, 'rtl');
			removeClass(editorContainer, 'ltr');
			addClass(editorContainer, dir);

			if (icons && icons.rtl) {
				icons.rtl(rtl);
			}

			return base;
		};

		/**
		 * Updates the toolbar to disable/enable the appropriate buttons
		 * @private
		 */
		updateToolBar = function (disable) {
			var mode = base.inSourceMode() ? '_sceTxtMode' : '_sceWysiwygMode';

			each(toolbarButtons, function (_, button) {
				toggleClass(button, 'disabled', disable || !button[mode]);
			});
		};

		/**
		 * Gets the width of the editor in pixels
		 *
		 * @since 1.3.5
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name width
		 * @return {number}
		 */
		/**
		 * Sets the width of the editor
		 *
		 * @param {number} width Width in pixels
		 * @since 1.3.5
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name width^2
		 * @return {this}
		 */
		/**
		 * Sets the width of the editor
		 *
		 * The saveWidth specifies if to save the width. The stored width can be
		 * used for things like restoring from maximized state.
		 *
		 * @param {number}     width            Width in pixels
		 * @param {boolean}	[saveWidth=true] If to store the width
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name width^3
		 * @return {this}
		 */
		base.width = function (width$$1, saveWidth) {
			if (!width$$1 && width$$1 !== 0) {
				return width(editorContainer);
			}

			base.dimensions(width$$1, null, saveWidth);

			return base;
		};

		/**
		 * Returns an object with the properties width and height
		 * which are the width and height of the editor in px.
		 *
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name dimensions
		 * @return {object}
		 */
		/**
		 * <p>Sets the width and/or height of the editor.</p>
		 *
		 * <p>If width or height is not numeric it is ignored.</p>
		 *
		 * @param {number}	width	Width in px
		 * @param {number}	height	Height in px
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name dimensions^2
		 * @return {this}
		 */
		/**
		 * <p>Sets the width and/or height of the editor.</p>
		 *
		 * <p>If width or height is not numeric it is ignored.</p>
		 *
		 * <p>The save argument specifies if to save the new sizes.
		 * The saved sizes can be used for things like restoring from
		 * maximized state. This should normally be left as true.</p>
		 *
		 * @param {number}		width		Width in px
		 * @param {number}		height		Height in px
		 * @param {boolean}	[save=true]	If to store the new sizes
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name dimensions^3
		 * @return {this}
		 */
		base.dimensions = function (width$$1, height$$1, save) {
			// set undefined width/height to boolean false
			width$$1 = (!width$$1 && width$$1 !== 0) ? false : width$$1;
			height$$1 = (!height$$1 && height$$1 !== 0) ? false : height$$1;

			if (width$$1 === false && height$$1 === false) {
				return { width: base.width(), height: base.height() };
			}

			if (width$$1 !== false) {
				if (save !== false) {
					options.width = width$$1;
				}

				width(editorContainer, width$$1);
			}

			if (height$$1 !== false) {
				if (save !== false) {
					options.height = height$$1;
				}

				height(editorContainer, height$$1);
			}

			return base;
		};

		/**
		 * Gets the height of the editor in px
		 *
		 * @since 1.3.5
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name height
		 * @return {number}
		 */
		/**
		 * Sets the height of the editor
		 *
		 * @param {number} height Height in px
		 * @since 1.3.5
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name height^2
		 * @return {this}
		 */
		/**
		 * Sets the height of the editor
		 *
		 * The saveHeight specifies if to save the height.
		 *
		 * The stored height can be used for things like
		 * restoring from maximized state.
		 *
		 * @param {number} height Height in px
		 * @param {boolean} [saveHeight=true] If to store the height
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name height^3
		 * @return {this}
		 */
		base.height = function (height$$1, saveHeight) {
			if (!height$$1 && height$$1 !== 0) {
				return height(editorContainer);
			}

			base.dimensions(null, height$$1, saveHeight);

			return base;
		};

		/**
		 * Gets if the editor is maximised or not
		 *
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name maximize
		 * @return {boolean}
		 */
		/**
		 * Sets if the editor is maximised or not
		 *
		 * @param {boolean} maximize If to maximise the editor
		 * @since 1.4.1
		 * @function
		 * @memberOf SCEditor.prototype
		 * @name maximize^2
		 * @return {this}
		 */
		base.maximize = function (maximize) {
			var maximizeSize = 'sceditor-maximize';

			if (isUndefined(maximize)) {
				return hasClass(editorContainer, maximizeSize);
			}

			maximize = !!maximize;

			if (maximize) {
				maximizeScrollPosition = globalWin.pageYOffset;
			}

			toggleClass(globalDoc.documentElement, maximizeSize, maximize);
			toggleClass(globalDoc.body, maximizeSize, maximize);
			toggleClass(editorContainer, maximizeSize, maximize);
			base.width(maximize ? '100%' : options.width, false);
			base.height(maximize ? '100%' : options.height, false);

			if (!maximize) {
				globalWin.scrollTo(0, maximizeScrollPosition);
			}

			autoExpand();

			return base;
		};

		autoExpand = function () {
			if (options.autoExpand && !autoExpandThrottle) {
				autoExpandThrottle = setTimeout(base.expandToContent, 200);
			}
		};

		/**
		 * Expands or shrinks the editors height to the height of it's content
		 *
		 * Unless ignoreMaxHeight is set to true it will not expand
		 * higher than the maxHeight option.
		 *
		 * @since 1.3.5
		 * @param {boolean} [ignoreMaxHeight=false]
		 * @function
		 * @name expandToContent
		 * @memberOf SCEditor.prototype
		 * @see #resizeToContent
		 */
		base.expandToContent = function (ignoreMaxHeight) {
			if (base.maximize()) {
				return;
			}

			clearTimeout(autoExpandThrottle);
			autoExpandThrottle = false;

			if (!autoExpandBounds) {
				var height$$1 = options.resizeMinHeight || options.height ||
					height(original);

				autoExpandBounds = {
					min: height$$1,
					max: options.resizeMaxHeight || (height$$1 * 2)
				};
			}

			var range = globalDoc.createRange();
			range.selectNodeContents(wysiwygBody);

			var rect = range.getBoundingClientRect();
			var current = wysiwygDocument.documentElement.clientHeight - 1;
			var spaceNeeded = rect.bottom - rect.top;
			var newHeight = base.height() + 1 + (spaceNeeded - current);

			if (!ignoreMaxHeight && autoExpandBounds.max !== -1) {
				newHeight = Math.min(newHeight, autoExpandBounds.max);
			}

			base.height(Math.ceil(Math.max(newHeight, autoExpandBounds.min)));
		};

		/**
		 * Destroys the editor, removing all elements and
		 * event handlers.
		 *
		 * Leaves only the original textarea.
		 *
		 * @function
		 * @name destroy
		 * @memberOf SCEditor.prototype
		 */
		base.destroy = function () {
			// Don't destroy if the editor has already been destroyed
			if (!pluginManager) {
				return;
			}

			pluginManager.destroy();

			rangeHelper = null;
			lastRange = null;
			pluginManager = null;

			if (dropdown) {
				remove(dropdown);
			}

			off(globalDoc, 'click', handleDocumentClick);

			// TODO: make off support null nodes?
			var form = original.form;
			if (form) {
				off(form, 'reset', handleFormReset);
				off(form, 'submit', base.updateOriginal);
			}

			remove(sourceEditor);
			remove(toolbar);
			remove(editorContainer);

			delete original._sceditor;
			show(original);

			original.required = isRequired;
		};


		/**
		 * Creates a menu item drop down
		 *
		 * @param  {HTMLElement} menuItem The button to align the dropdown with
		 * @param  {string} name          Used for styling the dropdown, will be
		 *                                a class sceditor-name
		 * @param  {HTMLElement} content  The HTML content of the dropdown
		 * @param  {boolean} ieFix           If to add the unselectable attribute
		 *                                to all the contents elements. Stops
		 *                                IE from deselecting the text in the
		 *                                editor
		 * @function
		 * @name createDropDown
		 * @memberOf SCEditor.prototype
		 */
		base.createDropDown = function (menuItem, name, content, ieFix) {
			// first click for create second click for close
			var dropDownCss,
				dropDownClass = 'sceditor-' + name;

			// Will re-focus the editor. This is needed for IE
			// as it has special logic to save/restore the selection
			base.closeDropDown(true);

			// Only close the dropdown if it was already open
			if (dropdown && hasClass(dropdown, dropDownClass)) {
				return;
			}

			// IE needs unselectable attr to stop it from
			// unselecting the text in the editor.
			// SCEditor can cope if IE does unselect the
			// text it's just not nice.
			if (ieFix !== false) {
				each(find(content, ':not(input):not(textarea)'),
					function (_, node) {
						if (node.nodeType === ELEMENT_NODE) {
							attr(node, 'unselectable', 'on');
						}
					});
			}

			dropDownCss = extend({
				top: menuItem.offsetTop,
				left: menuItem.offsetLeft,
				marginTop: menuItem.clientHeight
			}, options.dropDownCss);

			dropdown = createElement('div', {
				className: 'sceditor-dropdown ' + dropDownClass
			});

			css(dropdown, dropDownCss);
			appendChild(dropdown, content);
			appendChild(editorContainer, dropdown);
			on(dropdown, 'click focusin', function (e) {
				// stop clicks within the dropdown from being handled
				e.stopPropagation();
			});

			// If try to focus the first input immediately IE will
			// place the cursor at the start of the editor instead
			// of focusing on the input.
			setTimeout(function () {
				if (dropdown) {
					var first = find(dropdown, 'input,textarea')[0];
					if (first) {
						first.focus();
					}
				}
			});
		};

		/**
		 * Handles any document click and closes the dropdown if open
		 * @private
		 */
		handleDocumentClick = function (e) {
			// ignore right clicks
			if (e.which !== 3 && dropdown && !e.defaultPrevented) {
				autoUpdate();

				base.closeDropDown();
			}
		};

		/**
		 * Handles the WYSIWYG editors paste event
		 * @private
		 */
		handlePasteEvt = function (e) {
			var isIeOrEdge = IE_VER || edge;
			var editable = wysiwygBody;
			var clipboard = e.clipboardData;
			var loadImage = function (file) {
				var reader = new FileReader();
				reader.onload = function (e) {
					handlePasteData({
						html: '<img src="' + e.target.result + '" />'
					});
				};
				reader.readAsDataURL(file);
			};

			// Modern browsers with clipboard API - everything other than _very_
			// old android web views and UC browser which doesn't support the
			// paste event at all.
			if (clipboard && !isIeOrEdge) {
				var data$$1 = {};
				var types = clipboard.types;
				var items = clipboard.items;

				e.preventDefault();

				for (var i = 0; i < types.length; i++) {
					// Normalise image pasting to paste as a data-uri
					if (globalWin.FileReader && items &&
						IMAGE_MIME_REGEX.test(items[i].type)) {
						return loadImage(clipboard.items[i].getAsFile());
					}

					data$$1[types[i]] = clipboard.getData(types[i]);
				}
				// Call plugins here with file?
				data$$1.text = data$$1['text/plain'];
				data$$1.html = data$$1['text/html'];

				handlePasteData(data$$1);
				// If contentsFragment exists then we are already waiting for a
				// previous paste so let the handler for that handle this one too
			} else if (!pasteContentFragment) {
				// Save the scroll position so can be restored
				// when contents is restored
				var scrollTop = editable.scrollTop;

				rangeHelper.saveRange();

				pasteContentFragment = globalDoc.createDocumentFragment();
				while (editable.firstChild) {
					appendChild(pasteContentFragment, editable.firstChild);
				}

				setTimeout(function () {
					var html = editable.innerHTML;

					editable.innerHTML = '';
					appendChild(editable, pasteContentFragment);
					editable.scrollTop = scrollTop;
					pasteContentFragment = false;

					rangeHelper.restoreRange();

					handlePasteData({ html: html });
				}, 0);
			}
		};

		/**
		 * Gets the pasted data, filters it and then inserts it.
		 * @param {Object} data
		 * @private
		 */
		handlePasteData = function (data$$1) {
			var pasteArea = createElement('div', {}, wysiwygDocument);

			pluginManager.call('pasteRaw', data$$1);
			trigger(editorContainer, 'pasteraw', data$$1);

			if (data$$1.html) {
				pasteArea.innerHTML = data$$1.html;

				// fix any invalid nesting
				fixNesting(pasteArea);
			} else {
				pasteArea.innerHTML = entities(data$$1.text || '');
			}

			var paste = {
				val: pasteArea.innerHTML
			};

			if ('fragmentToSource' in format) {
				paste.val = format
					.fragmentToSource(paste.val, wysiwygDocument, currentNode);
			}

			pluginManager.call('paste', paste);
			trigger(editorContainer, 'paste', paste);

			if ('fragmentToHtml' in format) {
				paste.val = format
					.fragmentToHtml(paste.val, currentNode);
			}

			pluginManager.call('pasteHtml', paste);

			base.wysiwygEditorInsertHtml(paste.val, null, true);
		};

		/**
		 * Closes any currently open drop down
		 *
		 * @param {boolean} [focus=false] If to focus the editor
		 *                             after closing the drop down
		 * @function
		 * @name closeDropDown
		 * @memberOf SCEditor.prototype
		 */
		base.closeDropDown = function (focus) {
			if (dropdown) {
				remove(dropdown);
				dropdown = null;
			}

			if (focus === true) {
				base.focus();
			}
		};


		/**
		 * Inserts HTML into WYSIWYG editor.
		 *
		 * If endHtml is specified, any selected text will be placed
		 * between html and endHtml. If there is no selected text html
		 * and endHtml will just be concatenate together.
		 *
		 * @param {string} html
		 * @param {string} [endHtml=null]
		 * @param {boolean} [overrideCodeBlocking=false] If to insert the html
		 *                                               into code tags, by
		 *                                               default code tags only
		 *                                               support text.
		 * @function
		 * @name wysiwygEditorInsertHtml
		 * @memberOf SCEditor.prototype
		 */
		base.wysiwygEditorInsertHtml = function (
			html, endHtml, overrideCodeBlocking
		) {
			var marker, scrollTop, scrollTo,
				editorHeight = height(wysiwygEditor);

			base.focus();

			// TODO: This code tag should be configurable and
			// should maybe convert the HTML into text instead
			// Don't apply to code elements
			if (!overrideCodeBlocking && closest(currentBlockNode, 'code')) {
				return;
			}

			// Insert the HTML and save the range so the editor can be scrolled
			// to the end of the selection. Also allows emoticons to be replaced
			// without affecting the cursor position
			rangeHelper.insertHTML(html, endHtml);
			rangeHelper.saveRange();
			replaceEmoticons();

			// Scroll the editor after the end of the selection
			marker = find(wysiwygBody, '#sceditor-end-marker')[0];
			show(marker);
			scrollTop = wysiwygBody.scrollTop;
			scrollTo = (getOffset(marker).top +
				(marker.offsetHeight * 1.5)) - editorHeight;
			hide(marker);

			// Only scroll if marker isn't already visible
			if (scrollTo > scrollTop || scrollTo + editorHeight < scrollTop) {
				wysiwygBody.scrollTop = scrollTo;
			}

			triggerValueChanged(false);
			rangeHelper.restoreRange();

			// Add a new line after the last block element
			// so can always add text after it
			appendNewLine();
		};

		/**
		 * Like wysiwygEditorInsertHtml except it will convert any HTML
		 * into text before inserting it.
		 *
		 * @param {string} text
		 * @param {string} [endText=null]
		 * @function
		 * @name wysiwygEditorInsertText
		 * @memberOf SCEditor.prototype
		 */
		base.wysiwygEditorInsertText = function (text, endText) {
			base.wysiwygEditorInsertHtml(
				entities(text), entities(endText)
			);
		};

		/**
		 * Inserts text into the WYSIWYG or source editor depending on which
		 * mode the editor is in.
		 *
		 * If endText is specified any selected text will be placed between
		 * text and endText. If no text is selected text and endText will
		 * just be concatenate together.
		 *
		 * @param {string} text
		 * @param {string} [endText=null]
		 * @since 1.3.5
		 * @function
		 * @name insertText
		 * @memberOf SCEditor.prototype
		 */
		base.insertText = function (text, endText) {
			if (base.inSourceMode()) {
				base.sourceEditorInsertText(text, endText);
			} else {
				base.wysiwygEditorInsertText(text, endText);
			}

			return base;
		};

		/**
		 * Like wysiwygEditorInsertHtml but inserts text into the
		 * source mode editor instead.
		 *
		 * If endText is specified any selected text will be placed between
		 * text and endText. If no text is selected text and endText will
		 * just be concatenate together.
		 *
		 * The cursor will be placed after the text param. If endText is
		 * specified the cursor will be placed before endText, so passing:<br />
		 *
		 * '[b]', '[/b]'
		 *
		 * Would cause the cursor to be placed:<br />
		 *
		 * [b]Selected text|[/b]
		 *
		 * @param {string} text
		 * @param {string} [endText=null]
		 * @since 1.4.0
		 * @function
		 * @name sourceEditorInsertText
		 * @memberOf SCEditor.prototype
		 */
		base.sourceEditorInsertText = function (text, endText) {
			var scrollTop, currentValue,
				startPos = sourceEditor.selectionStart,
				endPos = sourceEditor.selectionEnd;

			scrollTop = sourceEditor.scrollTop;
			sourceEditor.focus();
			currentValue = sourceEditor.value;

			if (endText) {
				text += currentValue.substring(startPos, endPos) + endText;
			}

			sourceEditor.value = currentValue.substring(0, startPos) +
				text +
				currentValue.substring(endPos, currentValue.length);

			sourceEditor.selectionStart = (startPos + text.length) -
				(endText ? endText.length : 0);
			sourceEditor.selectionEnd = sourceEditor.selectionStart;

			sourceEditor.scrollTop = scrollTop;
			sourceEditor.focus();

			triggerValueChanged();
		};

		/**
		 * Gets the current instance of the rangeHelper class
		 * for the editor.
		 *
		 * @return {RangeHelper}
		 * @function
		 * @name getRangeHelper
		 * @memberOf SCEditor.prototype
		 */
		base.getRangeHelper = function () {
			return rangeHelper;
		};

		/**
		 * Gets or sets the source editor caret position.
		 *
		 * @param {Object} [position]
		 * @return {this}
		 * @function
		 * @since 1.4.5
		 * @name sourceEditorCaret
		 * @memberOf SCEditor.prototype
		 */
		base.sourceEditorCaret = function (position) {
			sourceEditor.focus();

			if (position) {
				sourceEditor.selectionStart = position.start;
				sourceEditor.selectionEnd = position.end;

				return this;
			}

			return {
				start: sourceEditor.selectionStart,
				end: sourceEditor.selectionEnd
			};
		};

		/**
		 * Gets the value of the editor.
		 *
		 * If the editor is in WYSIWYG mode it will return the filtered
		 * HTML from it (converted to BBCode if using the BBCode plugin).
		 * It it's in Source Mode it will return the unfiltered contents
		 * of the source editor (if using the BBCode plugin this will be
		 * BBCode again).
		 *
		 * @since 1.3.5
		 * @return {string}
		 * @function
		 * @name val
		 * @memberOf SCEditor.prototype
		 */
		/**
		 * Sets the value of the editor.
		 *
		 * If filter set true the val will be passed through the filter
		 * function. If using the BBCode plugin it will pass the val to
		 * the BBCode filter to convert any BBCode into HTML.
		 *
		 * @param {string} val
		 * @param {boolean} [filter=true]
		 * @return {this}
		 * @since 1.3.5
		 * @function
		 * @name val^2
		 * @memberOf SCEditor.prototype
		 */
		base.val = function (val, filter) {
			if (!isString(val)) {
				return base.inSourceMode() ?
					base.getSourceEditorValue(false) :
					base.getWysiwygEditorValue(filter);
			}

			if (!base.inSourceMode()) {
				if (filter !== false && 'toHtml' in format) {
					val = format.toHtml(val);
				}

				base.setWysiwygEditorValue(val);
			} else {
				base.setSourceEditorValue(val);
			}

			return base;
		};

		/**
		 * Inserts HTML/BBCode into the editor
		 *
		 * If end is supplied any selected text will be placed between
		 * start and end. If there is no selected text start and end
		 * will be concatenate together.
		 *
		 * If the filter param is set to true, the HTML/BBCode will be
		 * passed through any plugin filters. If using the BBCode plugin
		 * this will convert any BBCode into HTML.
		 *
		 * @param {string} start
		 * @param {string} [end=null]
		 * @param {boolean} [filter=true]
		 * @param {boolean} [convertEmoticons=true] If to convert emoticons
		 * @return {this}
		 * @since 1.3.5
		 * @function
		 * @name insert
		 * @memberOf SCEditor.prototype
		 */
		/**
		 * Inserts HTML/BBCode into the editor
		 *
		 * If end is supplied any selected text will be placed between
		 * start and end. If there is no selected text start and end
		 * will be concatenate together.
		 *
		 * If the filter param is set to true, the HTML/BBCode will be
		 * passed through any plugin filters. If using the BBCode plugin
		 * this will convert any BBCode into HTML.
		 *
		 * If the allowMixed param is set to true, HTML any will not be
		 * escaped
		 *
		 * @param {string} start
		 * @param {string} [end=null]
		 * @param {boolean} [filter=true]
		 * @param {boolean} [convertEmoticons=true] If to convert emoticons
		 * @param {boolean} [allowMixed=false]
		 * @return {this}
		 * @since 1.4.3
		 * @function
		 * @name insert^2
		 * @memberOf SCEditor.prototype
		 */
		// eslint-disable-next-line max-params
		base.insert = function (
			start, end, filter, convertEmoticons, allowMixed
		) {
			if (base.inSourceMode()) {
				base.sourceEditorInsertText(start, end);
				return base;
			}

			// Add the selection between start and end
			if (end) {
				var html = rangeHelper.selectedHtml();

				if (filter !== false && 'fragmentToSource' in format) {
					html = format
						.fragmentToSource(html, wysiwygDocument, currentNode);
				}

				start += html + end;
			}
			// TODO: This filter should allow empty tags as it's inserting.
			if (filter !== false && 'fragmentToHtml' in format) {
				start = format.fragmentToHtml(start, currentNode);
			}

			// Convert any escaped HTML back into HTML if mixed is allowed
			if (filter !== false && allowMixed === true) {
				start = start.replace(/&lt;/g, '<')
					.replace(/&gt;/g, '>')
					.replace(/&amp;/g, '&');
			}

			base.wysiwygEditorInsertHtml(start);

			return base;
		};

		/**
		 * Gets the WYSIWYG editors HTML value.
		 *
		 * If using a plugin that filters the Ht Ml like the BBCode plugin
		 * it will return the result of the filtering (BBCode) unless the
		 * filter param is set to false.
		 *
		 * @param {boolean} [filter=true]
		 * @return {string}
		 * @function
		 * @name getWysiwygEditorValue
		 * @memberOf SCEditor.prototype
		 */
		base.getWysiwygEditorValue = function (filter) {
			var html;
			// Create a tmp node to store contents so it can be modified
			// without affecting anything else.
			var tmp = createElement('div', {}, wysiwygDocument);
			var childNodes = wysiwygBody.childNodes;

			for (var i = 0; i < childNodes.length; i++) {
				appendChild(tmp, childNodes[i].cloneNode(true));
			}

			appendChild(wysiwygBody, tmp);
			fixNesting(tmp);
			remove(tmp);

			html = tmp.innerHTML;

			// filter the HTML and DOM through any plugins
			if (filter !== false && format.hasOwnProperty('toSource')) {
				html = format.toSource(html, wysiwygDocument);
			}

			return html;
		};

		/**
		 * Gets the WYSIWYG editor's iFrame Body.
		 *
		 * @return {HTMLElement}
		 * @function
		 * @since 1.4.3
		 * @name getBody
		 * @memberOf SCEditor.prototype
		 */
		base.getBody = function () {
			return wysiwygBody;
		};

		/**
		 * Gets the WYSIWYG editors container area (whole iFrame).
		 *
		 * @return {HTMLElement}
		 * @function
		 * @since 1.4.3
		 * @name getContentAreaContainer
		 * @memberOf SCEditor.prototype
		 */
		base.getContentAreaContainer = function () {
			return wysiwygEditor;
		};

		/**
		 * Gets the text editor value
		 *
		 * If using a plugin that filters the text like the BBCode plugin
		 * it will return the result of the filtering which is BBCode to
		 * HTML so it will return HTML. If filter is set to false it will
		 * just return the contents of the source editor (BBCode).
		 *
		 * @param {boolean} [filter=true]
		 * @return {string}
		 * @function
		 * @since 1.4.0
		 * @name getSourceEditorValue
		 * @memberOf SCEditor.prototype
		 */
		base.getSourceEditorValue = function (filter) {
			var val = sourceEditor.value;

			if (filter !== false && 'toHtml' in format) {
				val = format.toHtml(val);
			}

			return val;
		};

		/**
		 * Sets the WYSIWYG HTML editor value. Should only be the HTML
		 * contained within the body tags
		 *
		 * @param {string} value
		 * @function
		 * @name setWysiwygEditorValue
		 * @memberOf SCEditor.prototype
		 */
		base.setWysiwygEditorValue = function (value) {
			if (!value) {
				value = '<p>' + (IE_VER ? '' : '<br />') + '</p>';
			}

			wysiwygBody.innerHTML = value;
			replaceEmoticons();

			appendNewLine();
			triggerValueChanged();
			autoExpand();
		};

		/**
		 * Sets the text editor value
		 *
		 * @param {string} value
		 * @function
		 * @name setSourceEditorValue
		 * @memberOf SCEditor.prototype
		 */
		base.setSourceEditorValue = function (value) {
			sourceEditor.value = value;

			triggerValueChanged();
		};

		/**
		 * Updates the textarea that the editor is replacing
		 * with the value currently inside the editor.
		 *
		 * @function
		 * @name updateOriginal
		 * @since 1.4.0
		 * @memberOf SCEditor.prototype
		 */
		base.updateOriginal = function () {
			original.value = base.val();
		};

		/**
		 * Replaces any emoticon codes in the passed HTML
		 * with their emoticon images
		 * @private
		 */
		replaceEmoticons = function () {
			if (options.emoticonsEnabled) {
				replace(wysiwygBody, allEmoticons, options.emoticonsCompat);
			}
		};

		/**
		 * If the editor is in source code mode
		 *
		 * @return {boolean}
		 * @function
		 * @name inSourceMode
		 * @memberOf SCEditor.prototype
		 */
		base.inSourceMode = function () {
			return hasClass(editorContainer, 'sourceMode');
		};

		/**
		 * Gets if the editor is in sourceMode
		 *
		 * @return boolean
		 * @function
		 * @name sourceMode
		 * @memberOf SCEditor.prototype
		 */
		/**
		 * Sets if the editor is in sourceMode
		 *
		 * @param {boolean} enable
		 * @return {this}
		 * @function
		 * @name sourceMode^2
		 * @memberOf SCEditor.prototype
		 */
		base.sourceMode = function (enable) {
			var inSourceMode = base.inSourceMode();

			if (typeof enable !== 'boolean') {
				return inSourceMode;
			}

			if ((inSourceMode && !enable) || (!inSourceMode && enable)) {
				base.toggleSourceMode();
			}

			return base;
		};

		/**
		 * Switches between the WYSIWYG and source modes
		 *
		 * @function
		 * @name toggleSourceMode
		 * @since 1.4.0
		 * @memberOf SCEditor.prototype
		 */
		base.toggleSourceMode = function () {
			var isInSourceMode = base.inSourceMode();

			// don't allow switching to WYSIWYG if doesn't support it
			if (!isWysiwygSupported && isInSourceMode) {
				return;
			}

			if (!isInSourceMode) {
				rangeHelper.saveRange();
				rangeHelper.clear();
			}

			base.blur();

			if (isInSourceMode) {
				base.setWysiwygEditorValue(base.getSourceEditorValue());
			} else {
				base.setSourceEditorValue(base.getWysiwygEditorValue());
			}

			lastRange = null;
			toggle(sourceEditor);
			toggle(wysiwygEditor);

			toggleClass(editorContainer, 'wysiwygMode', isInSourceMode);
			toggleClass(editorContainer, 'sourceMode', !isInSourceMode);

			updateToolBar();
			updateActiveButtons();
		};

		/**
		 * Gets the selected text of the source editor
		 * @return {string}
		 * @private
		 */
		sourceEditorSelectedText = function () {
			sourceEditor.focus();

			return sourceEditor.value.substring(
				sourceEditor.selectionStart,
				sourceEditor.selectionEnd
			);
		};

		/**
		 * Handles the passed command
		 * @private
		 */
		handleCommand = function (caller, cmd) {
			// check if in text mode and handle text commands
			if (base.inSourceMode()) {
				if (cmd.txtExec) {
					if (Array.isArray(cmd.txtExec)) {
						base.sourceEditorInsertText.apply(base, cmd.txtExec);
					} else {
						cmd.txtExec.call(base, caller, sourceEditorSelectedText());
					}
				}
			} else if (cmd.exec) {
				if (isFunction(cmd.exec)) {
					cmd.exec.call(base, caller);
				} else {
					base.execCommand(
						cmd.exec,
						cmd.hasOwnProperty('execParam') ? cmd.execParam : null
					);
				}
			}

		};

		/**
		 * Saves the current range. Needed for IE because it forgets
		 * where the cursor was and what was selected
		 * @private
		 */
		saveRange = function () {
			/* this is only needed for IE */
			if (IE_VER) {
				lastRange = rangeHelper.selectedRange();
			}
		};

		/**
		 * Executes a command on the WYSIWYG editor
		 *
		 * @param {string} command
		 * @param {String|Boolean} [param]
		 * @function
		 * @name execCommand
		 * @memberOf SCEditor.prototype
		 */
		base.execCommand = function (command, param) {
			var executed = false,
				commandObj = base.commands[command];

			base.focus();

			// TODO: make configurable
			// don't apply any commands to code elements
			if (closest(rangeHelper.parentNode(), 'code')) {
				return;
			}

			try {
				executed = wysiwygDocument.execCommand(command, false, param);
			} catch (ex) { }

			// show error if execution failed and an error message exists
			if (!executed && commandObj && commandObj.errorMessage) {
				/*global alert:false*/
				alert(base._(commandObj.errorMessage));
			}

			updateActiveButtons();
		};

		/**
		 * Checks if the current selection has changed and triggers
		 * the selectionchanged event if it has.
		 *
		 * In browsers other than IE, it will check at most once every 100ms.
		 * This is because only IE has a selection changed event.
		 * @private
		 */
		checkSelectionChanged = function () {
			function check() {
				// Don't create new selection if there isn't one (like after
				// blur event in iOS)
				if (wysiwygWindow.getSelection() &&
					wysiwygWindow.getSelection().rangeCount <= 0) {
					currentSelection = null;
					// rangeHelper could be null if editor was destroyed
					// before the timeout had finished
				} else if (rangeHelper && !rangeHelper.compare(currentSelection)) {
					currentSelection = rangeHelper.cloneSelected();

					// If the selection is in an inline wrap it in a block.
					// Fixes #331
					if (currentSelection && currentSelection.collapsed) {
						var parent$$1 = currentSelection.startContainer;
						var offset = currentSelection.startOffset;

						// Handle if selection is placed before/after an element
						if (offset && parent$$1.nodeType !== TEXT_NODE) {
							parent$$1 = parent$$1.childNodes[offset];
						}

						while (parent$$1 && parent$$1.parentNode !== wysiwygBody) {
							parent$$1 = parent$$1.parentNode;
						}

						if (parent$$1 && isInline(parent$$1, true)) {
							rangeHelper.saveRange();
							wrapInlines(wysiwygBody, wysiwygDocument);
							rangeHelper.restoreRange();
						}
					}

					trigger(editorContainer, 'selectionchanged');
				}

				isSelectionCheckPending = false;
			}

			if (isSelectionCheckPending) {
				return;
			}

			isSelectionCheckPending = true;

			// Don't need to limit checking if browser supports the Selection API
			if ('onselectionchange' in wysiwygDocument) {
				check();
			} else {
				setTimeout(check, 100);
			}
		};

		/**
		 * Checks if the current node has changed and triggers
		 * the nodechanged event if it has
		 * @private
		 */
		checkNodeChanged = function () {
			// check if node has changed
			var oldNode,
				node = rangeHelper.parentNode();

			if (currentNode !== node) {
				oldNode = currentNode;
				currentNode = node;
				currentBlockNode = rangeHelper.getFirstBlockParent(node);

				trigger(editorContainer, 'nodechanged', {
					oldNode: oldNode,
					newNode: currentNode
				});
			}
		};

		/**
		 * Gets the current node that contains the selection/caret in
		 * WYSIWYG mode.
		 *
		 * Will be null in sourceMode or if there is no selection.
		 *
		 * @return {?Node}
		 * @function
		 * @name currentNode
		 * @memberOf SCEditor.prototype
		 */
		base.currentNode = function () {
			return currentNode;
		};

		/**
		 * Gets the first block level node that contains the
		 * selection/caret in WYSIWYG mode.
		 *
		 * Will be null in sourceMode or if there is no selection.
		 *
		 * @return {?Node}
		 * @function
		 * @name currentBlockNode
		 * @memberOf SCEditor.prototype
		 * @since 1.4.4
		 */
		base.currentBlockNode = function () {
			return currentBlockNode;
		};

		/**
		 * Updates if buttons are active or not
		 * @private
		 */
		updateActiveButtons = function () {
			var firstBlock, parent$$1;
			var activeClass = 'active';
			var doc = wysiwygDocument;
			var isSource = base.sourceMode();

			if (base.readOnly()) {
				each(find(toolbar, activeClass), function (_, menuItem) {
					removeClass(menuItem, activeClass);
				});
				return;
			}

			if (!isSource) {
				parent$$1 = rangeHelper.parentNode();
				firstBlock = rangeHelper.getFirstBlockParent(parent$$1);
			}

			for (var j = 0; j < btnStateHandlers.length; j++) {
				var state = 0;
				var btn = toolbarButtons[btnStateHandlers[j].name];
				var stateFn = btnStateHandlers[j].state;
				var isDisabled = (isSource && !btn._sceTxtMode) ||
					(!isSource && !btn._sceWysiwygMode);

				if (isString(stateFn)) {
					if (!isSource) {
						try {
							state = doc.queryCommandEnabled(stateFn) ? 0 : -1;

							// eslint-disable-next-line max-depth
							if (state > -1) {
								state = doc.queryCommandState(stateFn) ? 1 : 0;
							}
						} catch (ex) { }
					}
				} else if (!isDisabled) {
					state = stateFn.call(base, parent$$1, firstBlock);
				}

				toggleClass(btn, 'disabled', isDisabled || state < 0);
				toggleClass(btn, activeClass, state > 0);
			}

			if (icons && icons.update) {
				icons.update(isSource, parent$$1, firstBlock);
			}
		};

		/**
		 * Handles any key press in the WYSIWYG editor
		 *
		 * @private
		 */
		handleKeyPress = function (e) {
			// FF bug: https://bugzilla.mozilla.org/show_bug.cgi?id=501496
			if (e.defaultPrevented) {
				return;
			}

			base.closeDropDown();

			// 13 = enter key
			if (e.which === 13) {
				var LIST_TAGS = 'li,ul,ol';

				// "Fix" (cludge) for blocklevel elements being duplicated in some
				// browsers when enter is pressed instead of inserting a newline
				if (!is(currentBlockNode, LIST_TAGS) &&
					hasStyling(currentBlockNode)) {
					lastRange = null;

					var br = createElement('br', {}, wysiwygDocument);
					rangeHelper.insertNode(br);

					// Last <br> of a block will be collapsed unless it is
					// IE < 11 so need to make sure the <br> that was inserted
					// isn't the last node of a block.
					if (!IE_BR_FIX$2) {
						var parent$$1 = br.parentNode;
						var lastChild = parent$$1.lastChild;

						// Sometimes an empty next node is created after the <br>
						if (lastChild && lastChild.nodeType === TEXT_NODE &&
							lastChild.nodeValue === '') {
							remove(lastChild);
							lastChild = parent$$1.lastChild;
						}

						// If this is the last BR of a block and the previous
						// sibling is inline then will need an extra BR. This
						// is needed because the last BR of a block will be
						// collapsed. Fixes issue #248
						if (!isInline(parent$$1, true) && lastChild === br &&
							isInline(br.previousSibling)) {
							rangeHelper.insertHTML('<br>');
						}
					}

					e.preventDefault();
				}
			}
		};

		/**
		 * Makes sure that if there is a code or quote tag at the
		 * end of the editor, that there is a new line after it.
		 *
		 * If there wasn't a new line at the end you wouldn't be able
		 * to enter any text after a code/quote tag
		 * @return {void}
		 * @private
		 */
		appendNewLine = function () {
			// Check all nodes in reverse until either add a new line
			// or reach a non-empty textnode or BR at which point can
			// stop checking.
			rTraverse(wysiwygBody, function (node) {
				// Last block, add new line after if has styling
				if (node.nodeType === ELEMENT_NODE &&
					!/inline/.test(css(node, 'display'))) {

					// Add line break after if has styling
					if (!is(node, '.sceditor-nlf') && hasStyling(node)) {
						var paragraph = createElement('p', {}, wysiwygDocument);
						paragraph.className = 'sceditor-nlf';
						paragraph.innerHTML = !IE_BR_FIX$2 ? '<br />' : '';
						appendChild(wysiwygBody, paragraph);
						return false;
					}
				}

				// Last non-empty text node or line break.
				// No need to add line-break after them
				if ((node.nodeType === 3 && !/^\s*$/.test(node.nodeValue)) ||
					is(node, 'br')) {
					return false;
				}
			});
		};

		/**
		 * Handles form reset event
		 * @private
		 */
		handleFormReset = function () {
			base.val(original.value);
		};

		/**
		 * Handles any mousedown press in the WYSIWYG editor
		 * @private
		 */
		handleMouseDown = function () {
			base.closeDropDown();
			lastRange = null;
		};

		/**
		 * Translates the string into the locale language.
		 *
		 * Replaces any {0}, {1}, {2}, ect. with the params provided.
		 *
		 * @param {string} str
		 * @param {...String} args
		 * @return {string}
		 * @function
		 * @name _
		 * @memberOf SCEditor.prototype
		 */
		base._ = function () {
			var undef,
				args = arguments;

			if (locale && locale[args[0]]) {
				args[0] = locale[args[0]];
			}

			return args[0].replace(/\{(\d+)\}/g, function (str, p1) {
				return args[p1 - 0 + 1] !== undef ?
					args[p1 - 0 + 1] :
					'{' + p1 + '}';
			});
		};

		/**
		 * Passes events on to any handlers
		 * @private
		 * @return void
		 */
		handleEvent = function (e) {
			if (pluginManager) {
				// Send event to all plugins
				pluginManager.call(e.type + 'Event', e, base);
			}

			// convert the event into a custom event to send
			var name = (e.target === sourceEditor ? 'scesrc' : 'scewys') + e.type;

			if (eventHandlers[name]) {
				eventHandlers[name].forEach(function (fn) {
					fn.call(base, e);
				});
			}
		};

		/**
		 * Binds a handler to the specified events
		 *
		 * This function only binds to a limited list of
		 * supported events.
		 *
		 * The supported events are:
		 *
		 * * keyup
		 * * keydown
		 * * Keypress
		 * * blur
		 * * focus
		 * * nodechanged - When the current node containing
		 * 		the selection changes in WYSIWYG mode
		 * * contextmenu
		 * * selectionchanged
		 * * valuechanged
		 *
		 *
		 * The events param should be a string containing the event(s)
		 * to bind this handler to. If multiple, they should be separated
		 * by spaces.
		 *
		 * @param  {string} events
		 * @param  {Function} handler
		 * @param  {boolean} excludeWysiwyg If to exclude adding this handler
		 *                                  to the WYSIWYG editor
		 * @param  {boolean} excludeSource  if to exclude adding this handler
		 *                                  to the source editor
		 * @return {this}
		 * @function
		 * @name bind
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 */
		base.bind = function (events, handler, excludeWysiwyg, excludeSource) {
			events = events.split(' ');

			var i = events.length;
			while (i--) {
				if (isFunction(handler)) {
					var wysEvent = 'scewys' + events[i];
					var srcEvent = 'scesrc' + events[i];
					// Use custom events to allow passing the instance as the
					// 2nd argument.
					// Also allows unbinding without unbinding the editors own
					// event handlers.
					if (!excludeWysiwyg) {
						eventHandlers[wysEvent] = eventHandlers[wysEvent] || [];
						eventHandlers[wysEvent].push(handler);
					}

					if (!excludeSource) {
						eventHandlers[srcEvent] = eventHandlers[srcEvent] || [];
						eventHandlers[srcEvent].push(handler);
					}

					// Start sending value changed events
					if (events[i] === 'valuechanged') {
						triggerValueChanged.hasHandler = true;
					}
				}
			}

			return base;
		};

		/**
		 * Unbinds an event that was bound using bind().
		 *
		 * @param  {string} events
		 * @param  {Function} handler
		 * @param  {boolean} excludeWysiwyg If to exclude unbinding this
		 *                                  handler from the WYSIWYG editor
		 * @param  {boolean} excludeSource  if to exclude unbinding this
		 *                                  handler from the source editor
		 * @return {this}
		 * @function
		 * @name unbind
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 * @see bind
		 */
		base.unbind = function (events, handler, excludeWysiwyg, excludeSource) {
			events = events.split(' ');

			var i = events.length;
			while (i--) {
				if (isFunction(handler)) {
					if (!excludeWysiwyg) {
						arrayRemove(
							eventHandlers['scewys' + events[i]] || [], handler);
					}

					if (!excludeSource) {
						arrayRemove(
							eventHandlers['scesrc' + events[i]] || [], handler);
					}
				}
			}

			return base;
		};

		/**
		 * Blurs the editors input area
		 *
		 * @return {this}
		 * @function
		 * @name blur
		 * @memberOf SCEditor.prototype
		 * @since 1.3.6
		 */
		/**
		 * Adds a handler to the editors blur event
		 *
		 * @param  {Function} handler
		 * @param  {boolean} excludeWysiwyg If to exclude adding this handler
		 *                                  to the WYSIWYG editor
		 * @param  {boolean} excludeSource  if to exclude adding this handler
		 *                                  to the source editor
		 * @return {this}
		 * @function
		 * @name blur^2
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 */
		base.blur = function (handler, excludeWysiwyg, excludeSource) {
			if (isFunction(handler)) {
				base.bind('blur', handler, excludeWysiwyg, excludeSource);
			} else if (!base.sourceMode()) {
				wysiwygBody.blur();
			} else {
				sourceEditor.blur();
			}

			return base;
		};

		/**
		 * Focuses the editors input area
		 *
		 * @return {this}
		 * @function
		 * @name focus
		 * @memberOf SCEditor.prototype
		 */
		/**
		 * Adds an event handler to the focus event
		 *
		 * @param  {Function} handler
		 * @param  {boolean} excludeWysiwyg If to exclude adding this handler
		 *                                  to the WYSIWYG editor
		 * @param  {boolean} excludeSource  if to exclude adding this handler
		 *                                  to the source editor
		 * @return {this}
		 * @function
		 * @name focus^2
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 */
		base.focus = function (handler, excludeWysiwyg, excludeSource) {
			if (isFunction(handler)) {
				base.bind('focus', handler, excludeWysiwyg, excludeSource);
			} else if (!base.inSourceMode()) {
				// Already has focus so do nothing
				if (find(wysiwygDocument, ':focus').length) {
					return;
				}

				var container;
				var rng = rangeHelper.selectedRange();

				// Fix FF bug where it shows the cursor in the wrong place
				// if the editor hasn't had focus before. See issue #393
				if (!currentSelection) {
					autofocus();
				}

				// Check if cursor is set after a BR when the BR is the only
				// child of the parent. In Firefox this causes a line break
				// to occur when something is typed. See issue #321
				if (!IE_BR_FIX$2 && rng && rng.endOffset === 1 && rng.collapsed) {
					container = rng.endContainer;

					if (container && container.childNodes.length === 1 &&
						is(container.firstChild, 'br')) {
						rng.setStartBefore(container.firstChild);
						rng.collapse(true);
						rangeHelper.selectRange(rng);
					}
				}

				wysiwygWindow.focus();
				wysiwygBody.focus();

				// Needed for IE
				if (lastRange) {
					rangeHelper.selectRange(lastRange);

					// Remove the stored range after being set.
					// If the editor loses focus it should be saved again.
					lastRange = null;
				}
			} else {
				sourceEditor.focus();
			}

			updateActiveButtons();

			return base;
		};

		/**
		 * Adds a handler to the key down event
		 *
		 * @param  {Function} handler
		 * @param  {boolean} excludeWysiwyg If to exclude adding this handler
		 *                                  to the WYSIWYG editor
		 * @param  {boolean} excludeSource  If to exclude adding this handler
		 *                                  to the source editor
		 * @return {this}
		 * @function
		 * @name keyDown
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 */
		base.keyDown = function (handler, excludeWysiwyg, excludeSource) {
			return base.bind('keydown', handler, excludeWysiwyg, excludeSource);
		};

		/**
		 * Adds a handler to the key press event
		 *
		 * @param  {Function} handler
		 * @param  {boolean} excludeWysiwyg If to exclude adding this handler
		 *                                  to the WYSIWYG editor
		 * @param  {boolean} excludeSource  If to exclude adding this handler
		 *                                  to the source editor
		 * @return {this}
		 * @function
		 * @name keyPress
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 */
		base.keyPress = function (handler, excludeWysiwyg, excludeSource) {
			return base
				.bind('keypress', handler, excludeWysiwyg, excludeSource);
		};

		/**
		 * Adds a handler to the key up event
		 *
		 * @param  {Function} handler
		 * @param  {boolean} excludeWysiwyg If to exclude adding this handler
		 *                                  to the WYSIWYG editor
		 * @param  {boolean} excludeSource  If to exclude adding this handler
		 *                                  to the source editor
		 * @return {this}
		 * @function
		 * @name keyUp
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 */
		base.keyUp = function (handler, excludeWysiwyg, excludeSource) {
			return base.bind('keyup', handler, excludeWysiwyg, excludeSource);
		};

		/**
		 * Adds a handler to the node changed event.
		 *
		 * Happens whenever the node containing the selection/caret
		 * changes in WYSIWYG mode.
		 *
		 * @param  {Function} handler
		 * @return {this}
		 * @function
		 * @name nodeChanged
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 */
		base.nodeChanged = function (handler) {
			return base.bind('nodechanged', handler, false, true);
		};

		/**
		 * Adds a handler to the selection changed event
		 *
		 * Happens whenever the selection changes in WYSIWYG mode.
		 *
		 * @param  {Function} handler
		 * @return {this}
		 * @function
		 * @name selectionChanged
		 * @memberOf SCEditor.prototype
		 * @since 1.4.1
		 */
		base.selectionChanged = function (handler) {
			return base.bind('selectionchanged', handler, false, true);
		};

		/**
		 * Adds a handler to the value changed event
		 *
		 * Happens whenever the current editor value changes.
		 *
		 * Whenever anything is inserted, the value changed or
		 * 1.5 secs after text is typed. If a space is typed it will
		 * cause the event to be triggered immediately instead of
		 * after 1.5 seconds
		 *
		 * @param  {Function} handler
		 * @param  {boolean} excludeWysiwyg If to exclude adding this handler
		 *                                  to the WYSIWYG editor
		 * @param  {boolean} excludeSource  If to exclude adding this handler
		 *                                  to the source editor
		 * @return {this}
		 * @function
		 * @name valueChanged
		 * @memberOf SCEditor.prototype
		 * @since 1.4.5
		 */
		base.valueChanged = function (handler, excludeWysiwyg, excludeSource) {
			return base
				.bind('valuechanged', handler, excludeWysiwyg, excludeSource);
		};

		/**
		 * Emoticons keypress handler
		 * @private
		 */
		emoticonsKeyPress = function (e) {
			var replacedEmoticon,
				cachePos = 0,
				emoticonsCache = base.emoticonsCache,
				curChar = String.fromCharCode(e.which);

			// TODO: Make configurable
			if (closest(currentBlockNode, 'code')) {
				return;
			}

			if (!emoticonsCache) {
				emoticonsCache = [];

				each(allEmoticons, function (key, html) {
					emoticonsCache[cachePos++] = [key, html];
				});

				emoticonsCache.sort(function (a, b) {
					return a[0].length - b[0].length;
				});

				base.emoticonsCache = emoticonsCache;
				base.longestEmoticonCode =
					emoticonsCache[emoticonsCache.length - 1][0].length;
			}

			replacedEmoticon = rangeHelper.replaceKeyword(
				base.emoticonsCache,
				true,
				true,
				base.longestEmoticonCode,
				options.emoticonsCompat,
				curChar
			);

			if (replacedEmoticon) {
				if (!options.emoticonsCompat || !/^\s$/.test(curChar)) {
					e.preventDefault();
				}
			}
		};

		/**
		 * Makes sure emoticons are surrounded by whitespace
		 * @private
		 */
		emoticonsCheckWhitespace = function () {
			checkWhitespace(currentBlockNode, rangeHelper);
		};

		/**
		 * Gets if emoticons are currently enabled
		 * @return {boolean}
		 * @function
		 * @name emoticons
		 * @memberOf SCEditor.prototype
		 * @since 1.4.2
		 */
		/**
		 * Enables/disables emoticons
		 *
		 * @param {boolean} enable
		 * @return {this}
		 * @function
		 * @name emoticons^2
		 * @memberOf SCEditor.prototype
		 * @since 1.4.2
		 */
		base.emoticons = function (enable) {
			if (!enable && enable !== false) {
				return options.emoticonsEnabled;
			}

			options.emoticonsEnabled = enable;

			if (enable) {
				on(wysiwygBody, 'keypress', emoticonsKeyPress);

				if (!base.sourceMode()) {
					rangeHelper.saveRange();

					replaceEmoticons();
					triggerValueChanged(false);

					rangeHelper.restoreRange();
				}
			} else {
				var emoticons =
					find(wysiwygBody, 'img[data-sceditor-emoticon]');

				each(emoticons, function (_, img) {
					var text = data(img, 'sceditor-emoticon');
					var textNode = wysiwygDocument.createTextNode(text);
					img.parentNode.replaceChild(textNode, img);
				});

				off(wysiwygBody, 'keypress', emoticonsKeyPress);

				triggerValueChanged();
			}

			return base;
		};

		/**
		 * Gets the current WYSIWYG editors inline CSS
		 *
		 * @return {string}
		 * @function
		 * @name css
		 * @memberOf SCEditor.prototype
		 * @since 1.4.3
		 */
		/**
		 * Sets inline CSS for the WYSIWYG editor
		 *
		 * @param {string} css
		 * @return {this}
		 * @function
		 * @name css^2
		 * @memberOf SCEditor.prototype
		 * @since 1.4.3
		 */
		base.css = function (css$$1) {
			if (!inlineCss) {
				inlineCss = createElement('style', {
					id: 'inline'
				}, wysiwygDocument);

				appendChild(wysiwygDocument.head, inlineCss);
			}

			if (!isString(css$$1)) {
				return inlineCss.styleSheet ?
					inlineCss.styleSheet.cssText : inlineCss.innerHTML;
			}

			if (inlineCss.styleSheet) {
				inlineCss.styleSheet.cssText = css$$1;
			} else {
				inlineCss.innerHTML = css$$1;
			}

			return base;
		};

		/**
		 * Handles the keydown event, used for shortcuts
		 * @private
		 */
		handleKeyDown = function (e) {
			var shortcut = [],
				SHIFT_KEYS = {
					'`': '~',
					'1': '!',
					'2': '@',
					'3': '#',
					'4': '$',
					'5': '%',
					'6': '^',
					'7': '&',
					'8': '*',
					'9': '(',
					'0': ')',
					'-': '_',
					'=': '+',
					';': ': ',
					'\'': '"',
					',': '<',
					'.': '>',
					'/': '?',
					'\\': '|',
					'[': '{',
					']': '}'
				},
				SPECIAL_KEYS = {
					8: 'backspace',
					9: 'tab',
					13: 'enter',
					19: 'pause',
					20: 'capslock',
					27: 'esc',
					32: 'space',
					33: 'pageup',
					34: 'pagedown',
					35: 'end',
					36: 'home',
					37: 'left',
					38: 'up',
					39: 'right',
					40: 'down',
					45: 'insert',
					46: 'del',
					91: 'win',
					92: 'win',
					93: 'select',
					96: '0',
					97: '1',
					98: '2',
					99: '3',
					100: '4',
					101: '5',
					102: '6',
					103: '7',
					104: '8',
					105: '9',
					106: '*',
					107: '+',
					109: '-',
					110: '.',
					111: '/',
					112: 'f1',
					113: 'f2',
					114: 'f3',
					115: 'f4',
					116: 'f5',
					117: 'f6',
					118: 'f7',
					119: 'f8',
					120: 'f9',
					121: 'f10',
					122: 'f11',
					123: 'f12',
					144: 'numlock',
					145: 'scrolllock',
					186: ';',
					187: '=',
					188: ',',
					189: '-',
					190: '.',
					191: '/',
					192: '`',
					219: '[',
					220: '\\',
					221: ']',
					222: '\''
				},
				NUMPAD_SHIFT_KEYS = {
					109: '-',
					110: 'del',
					111: '/',
					96: '0',
					97: '1',
					98: '2',
					99: '3',
					100: '4',
					101: '5',
					102: '6',
					103: '7',
					104: '8',
					105: '9'
				},
				which = e.which,
				character = SPECIAL_KEYS[which] ||
					String.fromCharCode(which).toLowerCase();

			if (e.ctrlKey || e.metaKey) {
				shortcut.push('ctrl');
			}

			if (e.altKey) {
				shortcut.push('alt');
			}

			if (e.shiftKey) {
				shortcut.push('shift');

				if (NUMPAD_SHIFT_KEYS[which]) {
					character = NUMPAD_SHIFT_KEYS[which];
				} else if (SHIFT_KEYS[character]) {
					character = SHIFT_KEYS[character];
				}
			}

			// Shift is 16, ctrl is 17 and alt is 18
			if (character && (which < 16 || which > 18)) {
				shortcut.push(character);
			}

			shortcut = shortcut.join('+');
			if (shortcutHandlers[shortcut] &&
				shortcutHandlers[shortcut].call(base) === false) {

				e.stopPropagation();
				e.preventDefault();
			}
		};

		/**
		 * Adds a shortcut handler to the editor
		 * @param  {string}          shortcut
		 * @param  {String|Function} cmd
		 * @return {sceditor}
		 */
		base.addShortcut = function (shortcut, cmd) {
			shortcut = shortcut.toLowerCase();

			if (isString(cmd)) {
				shortcutHandlers[shortcut] = function () {
					handleCommand(toolbarButtons[cmd], base.commands[cmd]);

					return false;
				};
			} else {
				shortcutHandlers[shortcut] = cmd;
			}

			return base;
		};

		/**
		 * Removes a shortcut handler
		 * @param  {string} shortcut
		 * @return {sceditor}
		 */
		base.removeShortcut = function (shortcut) {
			delete shortcutHandlers[shortcut.toLowerCase()];

			return base;
		};

		/**
		 * Handles the backspace key press
		 *
		 * Will remove block styling like quotes/code ect if at the start.
		 * @private
		 */
		handleBackSpace = function (e) {
			var node, offset, range, parent$$1;

			// 8 is the backspace key
			if (options.disableBlockRemove || e.which !== 8 ||
				!(range = rangeHelper.selectedRange())) {
				return;
			}

			node = range.startContainer;
			offset = range.startOffset;

			if (offset !== 0 || !(parent$$1 = currentStyledBlockNode()) ||
				is(parent$$1, 'body')) {
				return;
			}

			while (node !== parent$$1) {
				while (node.previousSibling) {
					node = node.previousSibling;

					// Everything but empty text nodes before the cursor
					// should prevent the style from being removed
					if (node.nodeType !== TEXT_NODE || node.nodeValue) {
						return;
					}
				}

				if (!(node = node.parentNode)) {
					return;
				}
			}

			// The backspace was pressed at the start of
			// the container so clear the style
			base.clearBlockFormatting(parent$$1);
			e.preventDefault();
		};

		/**
		 * Gets the first styled block node that contains the cursor
		 * @return {HTMLElement}
		 */
		currentStyledBlockNode = function () {
			var block = currentBlockNode;

			while (!hasStyling(block) || isInline(block, true)) {
				if (!(block = block.parentNode) || is(block, 'body')) {
					return;
				}
			}

			return block;
		};

		/**
		 * Clears the formatting of the passed block element.
		 *
		 * If block is false, if will clear the styling of the first
		 * block level element that contains the cursor.
		 * @param  {HTMLElement} block
		 * @since 1.4.4
		 */
		base.clearBlockFormatting = function (block) {
			block = block || currentStyledBlockNode();

			if (!block || is(block, 'body')) {
				return base;
			}

			rangeHelper.saveRange();

			block.className = '';
			lastRange = null;

			attr(block, 'style', '');

			if (!is(block, 'p,div,td')) {
				convertElement(block, 'p');
			}

			rangeHelper.restoreRange();
			return base;
		};

		/**
		 * Triggers the valueChanged signal if there is
		 * a plugin that handles it.
		 *
		 * If rangeHelper.saveRange() has already been
		 * called, then saveRange should be set to false
		 * to prevent the range being saved twice.
		 *
		 * @since 1.4.5
		 * @param {boolean} saveRange If to call rangeHelper.saveRange().
		 * @private
		 */
		triggerValueChanged = function (saveRange) {
			if (!pluginManager ||
				(!pluginManager.hasHandler('valuechangedEvent') &&
					!triggerValueChanged.hasHandler)) {
				return;
			}

			var currentHtml,
				sourceMode = base.sourceMode(),
				hasSelection = !sourceMode && rangeHelper.hasSelection();

			// Composition end isn't guaranteed to fire but must have
			// ended when triggerValueChanged() is called so reset it
			isComposing = false;

			// Don't need to save the range if sceditor-start-marker
			// is present as the range is already saved
			saveRange = saveRange !== false &&
				!wysiwygDocument.getElementById('sceditor-start-marker');

			// Clear any current timeout as it's now been triggered
			if (valueChangedKeyUpTimer) {
				clearTimeout(valueChangedKeyUpTimer);
				valueChangedKeyUpTimer = false;
			}

			if (hasSelection && saveRange) {
				rangeHelper.saveRange();
			}

			currentHtml = sourceMode ? sourceEditor.value : wysiwygBody.innerHTML;

			// Only trigger if something has actually changed.
			if (currentHtml !== triggerValueChanged.lastVal) {
				triggerValueChanged.lastVal = currentHtml;

				trigger(editorContainer, 'valuechanged', {
					rawValue: sourceMode ? base.val() : currentHtml
				});
			}

			if (hasSelection && saveRange) {
				rangeHelper.removeMarkers();
			}
		};

		/**
		 * Should be called whenever there is a blur event
		 * @private
		 */
		valueChangedBlur = function () {
			if (valueChangedKeyUpTimer) {
				triggerValueChanged();
			}
		};

		/**
		 * Should be called whenever there is a keypress event
		 * @param  {Event} e The keypress event
		 * @private
		 */
		valueChangedKeyUp = function (e) {
			var which = e.which,
				lastChar = valueChangedKeyUp.lastChar,
				lastWasSpace = (lastChar === 13 || lastChar === 32),
				lastWasDelete = (lastChar === 8 || lastChar === 46);

			valueChangedKeyUp.lastChar = which;

			if (isComposing) {
				return;
			}

			// 13 = return & 32 = space
			if (which === 13 || which === 32) {
				if (!lastWasSpace) {
					triggerValueChanged();
				} else {
					valueChangedKeyUp.triggerNext = true;
				}
				// 8 = backspace & 46 = del
			} else if (which === 8 || which === 46) {
				if (!lastWasDelete) {
					triggerValueChanged();
				} else {
					valueChangedKeyUp.triggerNext = true;
				}
			} else if (valueChangedKeyUp.triggerNext) {
				triggerValueChanged();
				valueChangedKeyUp.triggerNext = false;
			}

			// Clear the previous timeout and set a new one.
			clearTimeout(valueChangedKeyUpTimer);

			// Trigger the event 1.5s after the last keypress if space
			// isn't pressed. This might need to be lowered, will need
			// to look into what the slowest average Chars Per Min is.
			valueChangedKeyUpTimer = setTimeout(function () {
				if (!isComposing) {
					triggerValueChanged();
				}
			}, 1500);
		};

		handleComposition = function (e) {
			isComposing = /start/i.test(e.type);

			if (!isComposing) {
				triggerValueChanged();
			}
		};

		autoUpdate = function () {
			base.updateOriginal();
		};

		// run the initializer
		init();
	}


	/**
	 * Map containing the loaded SCEditor locales
	 * @type {Object}
	 * @name locale
	 * @memberOf sceditor
	 */
	SCEditor.locale = {};

	SCEditor.formats = {};
	SCEditor.icons = {};


	/**
	 * Static command helper class
	 * @class command
	 * @name sceditor.command
	 */
	SCEditor.command =
	/** @lends sceditor.command */
	{
		/**
		 * Gets a command
		 *
		 * @param {string} name
		 * @return {Object|null}
		 * @since v1.3.5
		 */
		get: function (name) {
			return defaultCmds[name] || null;
		},

		/**
		 * <p>Adds a command to the editor or updates an existing
		 * command if a command with the specified name already exists.</p>
		 *
		 * <p>Once a command is add it can be included in the toolbar by
		 * adding it's name to the toolbar option in the constructor. It
		 * can also be executed manually by calling
		 * {@link sceditor.execCommand}</p>
		 *
		 * @example
		 * SCEditor.command.set("hello",
		 * {
		 *     exec: function () {
		 *         alert("Hello World!");
		 *     }
		 * });
		 *
		 * @param {string} name
		 * @param {Object} cmd
		 * @return {this|false} Returns false if name or cmd is false
		 * @since v1.3.5
		 */
		set: function (name, cmd) {
			if (!name || !cmd) {
				return false;
			}

			// merge any existing command properties
			cmd = extend(defaultCmds[name] || {}, cmd);

			cmd.remove = function () {
				SCEditor.command.remove(name);
			};

			defaultCmds[name] = cmd;
			return this;
		},

		/**
		 * Removes a command
		 *
		 * @param {string} name
		 * @return {this}
		 * @since v1.3.5
		 */
		remove: function (name) {
			if (defaultCmds[name]) {
				delete defaultCmds[name];
			}

			return this;
		}
	};

	/**
	 * SCEditor
	 * http://www.sceditor.com/
	 *
	 * Copyright (C) 2017, Sam Clarke (samclarke.com)
	 *
	 * SCEditor is licensed under the MIT license:
	 *	http://www.opensource.org/licenses/mit-license.php
	 *
	 * @fileoverview SCEditor - A lightweight WYSIWYG BBCode and HTML editor
	 * @author Sam Clarke
	 */

	window.sceditor = {
		command: SCEditor.command,
		commands: defaultCmds,
		defaultOptions: defaultOptions,

		ie: ie,
		ios: ios,
		isWysiwygSupported: isWysiwygSupported,

		regexEscape: regex,
		escapeEntities: entities,
		escapeUriScheme: uriScheme,

		dom: {
			css: css,
			attr: attr,
			removeAttr: removeAttr,
			is: is,
			closest: closest,
			width: width,
			height: height,
			traverse: traverse,
			rTraverse: rTraverse,
			parseHTML: parseHTML,
			hasStyling: hasStyling,
			convertElement: convertElement,
			blockLevelList: blockLevelList,
			canHaveChildren: canHaveChildren,
			isInline: isInline,
			copyCSS: copyCSS,
			fixNesting: fixNesting,
			findCommonAncestor: findCommonAncestor,
			getSibling: getSibling,
			removeWhiteSpace: removeWhiteSpace,
			extractContents: extractContents,
			getOffset: getOffset,
			getStyle: getStyle,
			hasStyle: hasStyle
		},
		locale: SCEditor.locale,
		icons: SCEditor.icons,
		utils: {
			each: each,
			isEmptyObject: isEmptyObject,
			extend: extend
		},
		plugins: PluginManager.plugins,
		formats: SCEditor.formats,
		create: function (textarea, options) {
			options = options || {};

			// Don't allow the editor to be initialised
			// on it's own source editor
			if (parent(textarea, '.sceditor-container')) {
				return;
			}

			if (options.runWithoutWysiwygSupport || isWysiwygSupported) {
				/*eslint no-new: off*/
				(new SCEditor(textarea, options));
			}
		},
		instance: function (textarea) {
			return textarea._sceditor;
		}
	};

	/**
	 * SCEditor
	 * http://www.sceditor.com/
	 *
	 * Copyright (C) 2017, Sam Clarke (samclarke.com)
	 *
	 * SCEditor is licensed under the MIT license:
	 *	http://www.opensource.org/licenses/mit-license.php
	 *
	 * @fileoverview SCEditor - A lightweight WYSIWYG BBCode and HTML editor
	 * @author Sam Clarke
	 * @requires jQuery
	 */

	// For backwards compatibility
	$.sceditor = window.sceditor;

	/**
	 * Creates an instance of sceditor on all textareas
	 * matched by the jQuery selector.
	 *
	 * If options is set to "state" it will return bool value
	 * indicating if the editor has been initialised on the
	 * matched textarea(s). If there is only one textarea
	 * it will return the bool value for that textarea.
	 * If more than one textarea is matched it will
	 * return an array of bool values for each textarea.
	 *
	 * If options is set to "instance" it will return the
	 * current editor instance for the textarea(s). Like the
	 * state option, if only one textarea is matched this will
	 * return just the instance for that textarea. If more than
	 * one textarea is matched it will return an array of
	 * instances each textarea.
	 *
	 * @param  {Object|string} [options] Should either be an Object of options or
	 *                                   the strings "state" or "instance"
	 * @return {this|Array<SCEditor>|Array<boolean>|SCEditor|boolean}
	 */
	$.fn.sceditor = function (options) {
		var instance;
		var ret = [];

		this.each(function () {
			instance = this._sceditor;

			// Add state of instance to ret if that is what options is set to
			if (options === 'state') {
				ret.push(!!instance);
			} else if (options === 'instance') {
				ret.push(instance);
			} else if (!instance) {
				$.sceditor.create(this, options);
			}
		});

		// If nothing in the ret array then must be init so return this
		if (!ret.length) {
			return this;
		}

		return ret.length === 1 ? ret[0] : ret;
	};

}(jQuery));
