<?php

namespace mcarpenter\vinephp;

function strptime($date, $fmt='Y-m-d H:i:s T') {
    return date($fmt, strtotime($date));
}

/**
 * Created by PhpStorm.
 * User: michael1
 * Date: 2015-05-22
 * Time: 4:15 PM
 */
abstract class Model {
    /**
     * @var API $api
     */
    protected $api = null;

    protected $data;

    protected function __construct($data) {
        $this->data = $data;
    }

    public static function fromId($id) {
        $class = get_called_class();

        $model = new $class(new \stdClass());
        $model->id = $id;
        return $model;
    }

    /***
     * @param $response
     * @return Model
     */
    public static function fromStdClass($response) {
        $class = get_called_class();

        /**
         * @var Model $model
         */
        $model = new $class($response);
        $model->parseChildren();
        return $model;
    }

    private function parseChildren() {
        // Vine adds className+'Id' as an id to the object
        $class = preg_split("/\\\\/", get_called_class());
        $className = strtolower($class[count($class) - 1]);
        $vineId = $className . 'Id';

        $keys = get_object_vars($this->data);
        foreach($keys as $key => $value) {
            if ($key == $vineId) {
                $this->data->id = $value;
            } elseif($key == 'userId') {
                $this->data->user = User::fromId($value);
            } elseif($key == 'postId') {
                $this->data->post = Post::fromId($value);
            } elseif($key == 'created') {
                $this->data->$key = strptime($value);
            } else if ($key == 'comments') {
                $this->data->$key = CommentCollection::fromStdClass($value);
            } else if ($key == 'likes') {
                $this->data->$key = LikeCollection::fromStdClass($value);
            } else if ($key == 'reposts') {
                $this->data->$key = RepostCollection::fromStdClass($value);
            } else if ($key == 'tags') {
                $this->data->$key = PureTagCollection::fromStdClass($value);
            } else if ($key == 'entities') {
                $this->data->$key = PureEntityCollection::fromStdClass($value);
            } else if ($key == 'user') {
                $this->data->$key = User::fromStdClass($value);
            }
        }

        $names = [
            'user' => 'username',
            'post' => 'description',
            'comment' => 'comment',
            'tag' => 'tag',
            'channel' => 'channel',
            'notification' => 'notificationTypeId',
            'like' => 'postId',
            'repost' => 'postId',
            'conversation' => 'conversationId',
            'message' => 'message'
        ];
        $nameAttr = isset($names[$className]) ? $names[$className] : 'unknown';
        $this->data->name = isset($this->data->$nameAttr) ? $this->data->$nameAttr : '<Unknown>';
    }

    /**
     * @param API $api
     */
    public function connectApi($api) {
        $this->api = $api;
    }

    public function __get($name) {
        if (isset($this->data->$name)) {
            return $this->data->$name;
        }
    }

    public function __set($name, $value) {
        if (isset($this->data->$name)) {
            $this->data->$name = $value;
        }
    }

    protected function ensureOwnership() {
        if($this->id != $this->api->getUserId()) {
            throw new VineException("You don't have permission to access that record.", 4);
        }
        return true;
    }
}

class ModelCollection extends \ArrayObject {
    protected static $model = Model::class;

    public static function fromStdClass($data) {
        $class = get_called_class();
        $model = $class::$model;
        $collection = new $class();
        foreach($data as $item) {
            $collection->append($model::fromStdClass($item));
        }
        return $collection;
    }

    function connectApi($api) {
        foreach($this as $item) {
            $item->connectApi($api);
        }
    }
}


# Model collection with metadata
abstract class MetaModelCollection extends Model implements \Countable, \IteratorAggregate
{
    protected static $modelKey = 'records';
    protected static $collectionClass = ModelCollection::class;

    /***
     * @param $response
     * @return Model
     */
    public static function fromStdClass($response) {
        $class = get_called_class();
        $model = $class::$collectionClass;

        $likeCollection = new $class($response);
        foreach($likeCollection->data as $key => $value) {
            if ($key == $class::$modelKey) {
                $value = $model::fromStdClass($value);
            }
            $likeCollection->$key = $value;
        }
        return $likeCollection;
    }

    public function connectApi($api) {
        $items = $this->getCollection();
        foreach ($items as $item) {
            $item->connectApi($api);
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() {
        return new \ArrayIterator($this->getCollection());
    }


    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     */
    public function count() {
        return count($this->getCollection());
    }

    protected function getCollection() {
        $key = self::$modelKey;
        return $this->data->$key;
    }
}

class User extends Model {

    /**
     * @param int $id
     * @param API $api
     * @return User
     */
    public static function getUserById($id, $api = null) {
        if (!$api) {
            $api = new API();
        }
        return $api->get_user($id);
    }

    public function connectApi($api) {
        parent::connectApi($api);

        if (isset($this->data->key)) {
            $this->api->authenticate($this);
        }
    }

    private function setUserId($user, &$args) {
        if (!$user) {
            $user = $this;
        }
        $args['user_id'] = $user->id;
    }

    public function follow($user = null, $args = array()) {
        $this->setUserId($user, $args);
        $this->api->follow($args);
        return $this;
    }

    public function unfollow($user, $args = array()) {
        $this->setUserId($user, $args);
        $this->api->unfollow($args);
        return $this;
    }

    public function followNotifications($user, $args = array()) {
        $this->setUserId($user, $args);
        $this->api->follow_notifications($args);
        return $this;
    }

    public function unfollowNotifications($user, $args = array()) {
        $this->setUserId($user, $args);
        $this->api->unfollow_notifications($args);
        return $this;
    }

    public function block($user, $args = array()) {
        $this->setUserId($user, $args);
        $this->api->block($args);
        return $this;
    }

    public function unblock($user, $args = array()) {
        $this->setUserId($user, $args);
        $this->api->block($args);
        return $this;
    }

    public function followers($args = array()) {
        $this->setUserId(null, $args);
        return $this->api->get_followers($args);
    }

    public function following($args = array()) {
        $this->setUserId(null, $args);
        return $this->api->get_following($args);
    }

    public function timeline($args = array()) {
        $this->setUserId(null, $args);
        return $this->api->get_user_timeline($args);
    }

    public function likes($args = array()) {
        $this->setUserId(null, $args);
        return $this->api->get_user_likes($args);
    }

    public function pendingNotificationsCount($args = array()) {
        $this->ensureOwnership();
        $this->setUserId(null, $args);
        return $this->api->get_pending_notifications_count($args);
    }

    public function getNotifications($args = array()) {
        $this->ensureOwnership();
        $this->setUserId(null, $args);
        return $this->api->get_notifications($args);
    }

    public function update($args = array()) {
        $this->ensureOwnership();
        $this->setUserId(null, $args);
        $this->api->update_profile($args);
        return $this;
    }

    public function unsetExplicit($args = array()) {
        $this->ensureOwnership();
        $this->setUserId(null, $args);
        $this->api->unset_explicit($args);
    }

    public function setExplicit($args = array()) {
        $this->ensureOwnership();
        $this->setUserId(null, $args);
        return $this->api->set_explicit($args);
    }
}

class Post extends Model {

    /**
     * @param int $id
     * @param API $api
     * @return Post
     */
    public static function getPostById($id, $api = null) {
        if (!$api) {
            $api = new API();
        }
        return $api->get_post(array("post_id" => $id));
    }

    private function setPostId($post, &$args) {
        if (!$post) {
            $post = $this;
        }
        $args['post_id'] = $post->id;
    }

    function like( $args = array() ) {
        $this->setPostId(null, $args);
        $post = $this->api->like($args);
        $post->post = $this;
        return $post;
    }

    function unlike( $args = array() ) {
        $this->setPostId(null, $args);
        return $this->api->unlike($args);
    }

    function revine( $args = array() ) {
        $this->setPostId(null, $args);
        $post = $this->api->revine($args);
        $post->post = $this;
        return $post;
    }

    function comment( $comment, $entities = [], $args = array() ) {
        $_comment = '';
        if (is_array($comment)) {
            $entities = [];
            foreach($comment as $element) {
                if (typeof($element) == 'string') {
                    $_comment .= $element;
                } else {
                    $entity = new \stdClass();
                    $entity->id = $element->id;
                    $entity->range = [strlen($_comment), strlen($_comment) + strlen($element->name)];
                    $entity->type = 'mention';
                    $entity->title = $element->name;

                    $_comment .= $element->name + ' ';
                    $entities[] = $entity;
                }
            }
        }
        else {
            $_comment = $comment;
        }

        $args['post_id'] = $this->id;
        $args['comment'] = $_comment;
        $args['entities'] = $entities;

        $post = $this->api->comment($args);
        $post->post = $this;
        return $post;
    }

    function report( $args = array() )  {
        $this->setPostId(null, $args);
        $this->api->report($args);
        return $this;
    }

    function likes( $args = array() ) {
        $this->setPostId(null, $args);
        return $this->api->get_post_likes($args);
    }

    function comments( $args = array() ) {
        $this->setPostId(null, $args);
        return $this->api->get_post_comments($args);
    }

    function reposts( $args = array() ) {
        $this->setPostId(null, $args);
        return $this->api->get_post_reposts($args);
    }
}

class Comment extends Model {
    function delete( $args = array() ) {
        $this->ensureOwnership();

        $args['post_id'] = $this->post->id;
        $args['comment_id'] = $this->id;
        return $this->api->uncomment($args);
    }
}

class Like extends Model {
    function delete( $args = array() ) {
        $this->ensureOwnership();

        $args['post_id'] = $this->post->id;
        return $this->api->unlike($args);
    }
}


class Repost extends Model {
    function delete($args = array() ) {
        $this->ensureOwnership();

        $args['post_id'] = $this->post->id;
        $args['revine_id'] = $this->id;
        return $this->api->unrevine($args);
    }
}

class Tag extends Model {
    function timeline($args = array()) {
        $args['tag_name'] = $this->tag;
        return $this->api->get_tag_timeline($args);
    }
}

class Channel extends Model {
    function timeline( $args = array() ) {
        $args['channel_id'] = $this->id;
        return $this->api->get_channel_recent_timeline($args);
    }
    function recentTimeline( $args = array() ) {
        return $this->timeline();
    }

    function popularTimeline( $args = array() ) {
        $args['channel_id'] = $this->id;
        return $this->api->get_channel_popular_timeline($args);
    }
}

class Notification extends Model {
}

// mention, tag or post in a notification, comment or title
class Entity extends Model {
}

class Venue extends Model {
}

class Conversation extends Model {
}

class Message extends Model {
}

class PureUserCollection extends ModelCollection {
    public static $model = User::class;
}

class UserCollection extends MetaModelCollection {
    protected static $collectionClass = PureUserCollection::class;
}

class PurePostCollection extends ModelCollection {
    protected static $model = Post::class;
}

// Timeline
class PostCollection extends MetaModelCollection {
    protected static $collectionClass = PurePostCollection::class;
}

class PureCommentCollection extends ModelCollection {
    protected static $model = Comment::class;
}

class CommentCollection extends MetaModelCollection {
    protected static $collectionClass = PureCommentCollection::class;
}

class PureLikeCollection extends ModelCollection {
    protected static $model = Like::class;
}

class LikeCollection extends MetaModelCollection {
    protected static $collectionClass = PureLikeCollection::class;
}

class PureRepostCollection extends ModelCollection {
    protected static $model = Repost::class;
}

class RepostCollection extends MetaModelCollection {
    protected static $collectionClass = PureRepostCollection::class;
}

class PureTagCollection extends ModelCollection {
    protected static $model = Tag::class;
}

class TagCollection extends MetaModelCollection {
    protected static $collectionClass = PureTagCollection::class;
}

class PureChannelCollection extends ModelCollection {
    protected static $model = Channel::class;
}

class ChannelCollection extends MetaModelCollection {
    protected static $collectionClass = PureChannelCollection::class;
}


class PureNotificationCollection extends ModelCollection {
    protected static $model = Notification::class;
}

class NotificationCollection extends MetaModelCollection {
    protected static $collectionClass = PureNotificationCollection::class;
}

class PureEntityCollection extends ModelCollection {
    protected static $model = Entity::class;
}

class PureConversationCollection extends ModelCollection {
    protected static $model = Conversation::class;
}

class ConversationCollection extends MetaModelCollection {
    protected static $collectionClass = PureConversationCollection::class;
}

class PureMessageCollection extends ModelCollection {
    protected static $model = Message::class;
}

class MessageCollection extends MetaModelCollection {
    protected static $collectionClass = PureMessageCollection::class;
    protected static $modelKey = 'messages';
}