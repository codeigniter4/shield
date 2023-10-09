/*! `curl` grammar compiled for Highlight.js 11.3.1 */
(()=>{var e=(()=>{"use strict";return e=>{const n={className:"string",begin:/"/,
end:/"/,contains:[e.BACKSLASH_ESCAPE,{className:"variable",begin:/\$\(/,
end:/\)/,contains:[e.BACKSLASH_ESCAPE]}],relevance:0},a={className:"number",
variants:[{begin:e.C_NUMBER_RE}],relevance:0};return{name:"curl",
aliases:["curl"],keywords:"curl",case_insensitive:!0,contains:[{
className:"literal",begin:/(--request|-X)\s/,contains:[{className:"symbol",
begin:/(get|post|delete|options|head|put|patch|trace|connect)/,end:/\s/,
returnEnd:!0}],returnEnd:!0,relevance:10},{className:"literal",begin:/--/,
end:/[\s"]/,returnEnd:!0,relevance:0},{className:"literal",begin:/-\w/,
end:/[\s"]/,returnEnd:!0,relevance:0},n,{className:"string",begin:/\\"/,
relevance:0},{className:"string",begin:/'/,end:/'/,relevance:0
},e.APOS_STRING_MODE,e.QUOTE_STRING_MODE,a,{match:/(\/[a-z._-]+)+/}]}}})()
;hljs.registerLanguage("curl",e)})();