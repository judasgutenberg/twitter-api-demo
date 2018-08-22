//javascript library for tweeter


function replaceAll(str, find, replace)
{
	return str.replace(new RegExp(find, 'g'), replace);
}

function addAttribute(node, attribName, value)
{
	var attrib = document.createAttribute(attribName);
	attrib.value = value;
	node.setAttributeNode(attrib);
}

function hideAllTextAreas()
{
	var responderDivs = document.getElementsByClassName("responder");
	for(var i=0; i<responderDivs.length; i++)
	{
		var listNode = responderDivs[i];
		listNode.style.display = 'none';
	}
}

function getSuggestedTweet(twitterId, type)
{
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			var textAreaNode = document.getElementById("textarea_" + twitterId);
			var objResponse = JSON.parse(xmlhttp.responseText);
			if(objResponse.length>0)
			{
				textAreaNode.innerHTML = objResponse[0].tweet_text;
			}
			else
			{
				textAreaNode.innerHTML = "";
			}
 		}
	}
	var processingUrl="tweeter_backend.php?action=suggestedtweets&type=" + type;
	xmlhttp.open("GET", processingUrl);
	xmlhttp.send();
}


function makeInput(twitterId, type)
{
	hideAllTextAreas();
	var node = document.getElementById("responder_" + twitterId);
	node.style.display = 'block';
	getSuggestedTweet(twitterId, type);
}

function twitterPost(twitterId)
{
	var textareaNode = document.getElementById("textarea_" + twitterId);
	var textPost = textareaNode.value.trim();
	if(textPost.length<1)
	{
		tallyOrMessage(twitterId, "Your post contained no text!");
		return;
	}
	//we can just send the post without worrying about whether it was received
	//if it isn't, it won't be marked as replied_to, so we could see it again
	var xmlhttp=new XMLHttpRequest();
	var processingUrl="tweeter_backend.php?action=reply&twitter_id=" + twitterId + "&reply=" + encodeURIComponent(textPost);
	//console.log(processingUrl);
	xmlhttp.open("GET", processingUrl);
	xmlhttp.send();
	
	//now delete the whole div containing the post
	var listNode = document.getElementById("list");
	var divNode = document.getElementById("div_" + twitterId);
	//if we just hid it, we'd do this:
	//divNode.style.display = 'none';
	//but we're deleting it
	listNode.removeChild(divNode);
	//now do the infinite scroll thing and find another bunch of posts to append on the bottom 
	//in case we have five or fewer posts visible
	if(countTweetNodes()<6)
	{
		//due to the requirement of a button to do this, i will not have this happen automatically
		//although if you want infinite scroll, uncomment out the following line:
		//getMoreTweets(searchString);
	}
 
}

function countTweetNodes()
{
	var responderDivs = document.getElementsByClassName("tweet");
	return responderDivs.length;
}

function getMoreTweets(searchString)
{
	//do an ajax request for tweets and then display them when they come in
	var xmlhttp=new XMLHttpRequest();
	xmlhttp.onreadystatechange=function()
	{
		if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{
			var objResponse = JSON.parse(xmlhttp.responseText);
			tweets = objResponse.statuses;
			displayTweets();
 		}
	}
	var processingUrl="tweeter_backend.php?q=" + encodeURIComponent(searchString);
	 
	xmlhttp.open("GET", processingUrl);
	xmlhttp.send();
}

function displayTweets()
{
	//count is maximum; if zero then all that were returned
	for(var i=0; i<tweets.length; i++)
	{
		var thisTweet = tweets[i];
		displayTweet(thisTweet);
	}
}

function tallyOrMessage(twitterId, message)
{
	//also can display message
	var textArea = document.getElementById("textarea_" + twitterId);
	var tallySize = textArea.value.length;
	var tallyPlace = document.getElementById("tally_" + twitterId);
	//textArea.value = textArea.value.trim(); //fixes a descrepancy in char counting, but causes a bad bug! 
	if(message)
	{
		tallyPlace.innerHTML = message;
	}
	else
	{
		tallyPlace.innerHTML = 140 - parseInt(tallySize) + " characters remaining";
	}
}

function displayTweet(thisTweet)
{
	var listNode = document.getElementById("list");
	var formHTML= "<button type='button' onclick='makeInput(\"TWITTER_ID\", 2)'>Response 1</button><button type='button' onclick='makeInput(\"TWITTER_ID\", 3)'>Response 2</button><button type='button' onclick='makeInput(\"TWITTER_ID\", 1)'>Custom</button><div class='responder' id='responder_TWITTER_ID' style='display:none'><div class='respond_button_div'><button class='respond' type='button' onclick='twitterPost(\"TWITTER_ID\")'>Respond</button></div><textarea onkeyup='tallyOrMessage(\"TWITTER_ID\")' maxlength='140' name='post' id='textarea_TWITTER_ID' class='post_text_area'></textarea><div class='tally' id='tally_TWITTER_ID'></div></div>";
	var twitterId = thisTweet.id_str;
	//only add tweets we don't actually have in our displayed twitter list
	if(viewedTwitterIds.indexOf(twitterId) == -1)
	{
		var newNode = document.createElement("div");
		addAttribute(newNode, "class", "tweet");
		addAttribute(newNode, "id", "div_" + twitterId);
		var userNode = document.createElement("div");
		addAttribute(userNode, "class", "user");
		userNode.innerHTML = thisTweet.user.name;
		newNode.appendChild(userNode);
		var createdNode = document.createElement("div");
		addAttribute(createdNode, "class", "date");
		createdNode.innerHTML = thisTweet.created_at;
		newNode.appendChild(createdNode);
		var textTextNode = document.createElement("div");
		addAttribute(textTextNode, "class", "tweet_body");
		textTextNode.innerHTML=thisTweet.text;
		newNode.appendChild(textTextNode);
		var formNode = document.createElement("div");
		thisFormHTML = replaceAll(formHTML,"TWITTER_ID", twitterId);
		formNode.innerHTML = thisFormHTML;
		newNode.appendChild(formNode);
		listNode.appendChild(newNode);
		viewedTwitterIds[viewedTwitterIds.length] = twitterId;
	}
}