google.load("feeds", "1");
function initialize2() {
	var feed = new google.feeds.Feed("http://rumi-shishido.com/news/?feed=rss2");
	feed.setNumEntries(4)
	feed.load(function(result) {
		if (!result.error) {
			var container = document.getElementById("newsfeed");
			for (var i = 0; i < result.feed.entries.length; i++) {
				var entry = result.feed.entries[i];
				var str1 = entry.title;
				if (str1.substring(0,3) == "PR:"){
					continue;
				} else {
					var div = document.createElement("li");
					var a = document.createElement("a");
					a.href = entry.link;
					a.target = "";
					var list = entry.title + "(" + entry.publishedDate.substring(5, 11) + ")" ;
					a.appendChild(document.createTextNode(list));
					div.appendChild(a);
					container.appendChild(div);
				}
			}
		}
	});
}
google.setOnLoadCallback(initialize2);
