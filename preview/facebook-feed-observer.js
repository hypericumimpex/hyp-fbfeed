/*
    Elfsight Facebook Feed
    Version: 1.9.4
    Release date: Fri Feb 01 2019

    https://elfsight.com

    Copyright (c) 2019 Elfsight, LLC. ALL RIGHTS RESERVED
*/

/*!
 * 
 * 	elfsight.com
 * 
 * 	Copyright (c) 2019 Elfsight, LLC. ALL RIGHTS RESERVED
 * 
 */
!function(e){var r={};function o(t){if(r[t])return r[t].exports;var n=r[t]={i:t,l:!1,exports:{}};return e[t].call(n.exports,n,n.exports,o),n.l=!0,n.exports}o.m=e,o.c=r,o.d=function(e,r,t){o.o(e,r)||Object.defineProperty(e,r,{configurable:!1,enumerable:!0,get:t})},o.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return o.d(r,"a",r),r},o.o=function(e,r){return Object.prototype.hasOwnProperty.call(e,r)},o.p="",o(o.s=0)}([function(e,r){!function(e){(window.eapps=window.eapps||{}).observer=function(e,o,t){e.$watch("widget.data.userAccessToken",function(){e.widget.data.userAccessToken?(r("sourceType",!1,o),r("source",!0,o)):(r("sourceType",!1,o),r("source",!1,o))}),e.$watch("widget.data.sourceType",function(){e.widget.data.userAccessToken&&("profile"===e.widget.data.sourceType?r("source",!1,o):r("source",!0,o))})};var r=function e(r,o,t){t.forEach(function(n,u){if(n.id===r)return t[u].visible=o,!1;n&&n.properties&&e(r,o,n.properties),n.complex&&n.complex.properties&&e(r,o,n.complex.properties),n.subgroup&&n.subgroup.properties&&e(r,o,n.subgroup.properties)})}}()}]);