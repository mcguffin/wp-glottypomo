

/*
Copyright (c) 2008 Fred Palmer fred.palmer_at_gmail.com

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
//*
(function(exports) {
	if ( window.btoa && window.atob ) {
		exports.Base64 = {
			encode:function(s){return window.btoa(s)},
			decode:function(s){return window.atob(s)}
		};
	} else {
		// Adapted from  https://github.com/davidchambers/Base64.js/edit/master/base64.js
		exports.Base64 = {}
		var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

		function InvalidCharacterError(message) {
			this.message = message;
		}
		InvalidCharacterError.prototype = new Error;
		InvalidCharacterError.prototype.name = 'InvalidCharacterError';

	  // encoder
	  // [https://gist.github.com/999166] by [https://github.com/nignag]
	  
	  exports.Base64.encode = function (input) {
		var str = String(input);
		for (
		  // initialize result and counter
		  var block, charCode, idx = 0, map = chars, output = '';
		  // if the next str index does not exist:
		  //   change the mapping table to "="
		  //   check if d has no fractional digits
		  str.charAt(idx | 0) || (map = '=', idx % 1);
		  // "8 - idx % 1 * 8" generates the sequence 2, 4, 6, 8
		  output += map.charAt(63 & block >> 8 - idx % 1 * 8)
		) {
		  charCode = str.charCodeAt(idx += 3/4);
		  if (charCode > 0xFF) {
			throw new InvalidCharacterError("'btoa' failed: The string to be encoded contains characters outside of the Latin1 range.");
		  }
		  block = block << 8 | charCode;
		}
		return output;
	  };

	  // decoder
	  // [https://gist.github.com/1020396] by [https://github.com/atk]
	  exports.Base64.decode = function (input) {
		var str = String(input).replace(/=+$/, '');
		if (str.length % 4 == 1) {
		  throw new InvalidCharacterError("'atob' failed: The string to be decoded is not correctly encoded.");
		}
		for (
		  // initialize result and counters
		  var bc = 0, bs, buffer, idx = 0, output = '';
		  // get next character
		  buffer = str.charAt(idx++);
		  // character found in table? initialize bit storage and add its ascii value;
		  ~buffer && (bs = bc % 4 ? bs * 64 + buffer : buffer,
			// and if not first of each 4 characters,
			// convert the first 8 bits to one ascii character
			bc++ % 4) ? output += String.fromCharCode(255 & bs >> (-2 * bc & 6)) : 0
		) {
		  // try to find character in table (0-63, not found => -1)
		  buffer = chars.indexOf(buffer);
		}
		return output;
	  };
	}
})(window);

