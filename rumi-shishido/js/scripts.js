google.load("feeds", "1");
function initialize() {
	var feed = new google.feeds.Feed("http://rssblog.ameba.jp/sundaliru/rss20.xml");
	feed.setNumEntries(9)
	feed.load(function(result) {
		if (!result.error) {
			var container = document.getElementById("feed");
			for (var i = 0; i < result.feed.entries.length; i++) {
				var entry = result.feed.entries[i];
				var str1 = entry.title;
				if (str1.substring(0,3) == "PR:"){
					continue;
				} else {
					var div = document.createElement("li");
					var a = document.createElement("a");
					a.href = entry.link;
					a.target = "_blank";
					var list = entry.title + "(" + entry.publishedDate.substring(5, 11) + ")" ;
					a.appendChild(document.createTextNode(list));
					div.appendChild(a);
					container.appendChild(div);
				}
			}
		}
	});
}
google.setOnLoadCallback(initialize);
