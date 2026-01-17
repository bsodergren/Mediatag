
  function copyText () {
      var videoPlaylist = document.getElementById('videoPlaylist')
    var linkList = videoPlaylist.querySelectorAll('div > a')
    for (var i = 0; i < linkList.length; i++) {
      // innerHTML = linkList[i].href;
      // console.log(linkList[i].href,linkList[i].href.match(/view_video/) )
      if (linkList[i].href.match(/view_video/) !== null) {
        text = text + '\n' + linkList[i].href
      }
    }
}
