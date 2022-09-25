function suggestPassword() {
  const array = new Uint32Array(10);
  window.crypto.getRandomValues(array);
  return 'P' + dsteem.PrivateKey.fromSeed(array).toString();
}

window.onload = function() {
	document.getElementById("password").value = suggestPassword();
}