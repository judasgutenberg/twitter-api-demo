
CREATE TABLE `suggested_tweet` (
  `suggested_tweet_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL,
  `tweet_text` varchar(140) DEFAULT NULL,
  PRIMARY KEY (`suggested_tweet_id`)
);

INSERT INTO suggested_tweet(`suggested_tweet_id`,`type_id`,`tweet_text`) VALUES('1','2','That is such an awesome thing to say!  I couldn&#39;t have fit it into 140 characters!');
INSERT INTO suggested_tweet(`suggested_tweet_id`,`type_id`,`tweet_text`) VALUES('2','3','That may be one of the least enlightened things I&#39;ve seen tweeted all week!');


CREATE TABLE `tweet` (
  `twitter_id` varchar(50) NOT NULL,
  `text` varchar(150) NOT NULL,
  `reply` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `replied_to` datetime DEFAULT NULL,
  PRIMARY KEY (`twitter_id`)
);



