<?php

namespace mcarpenter\vinephp;

/**
 * Created by PhpStorm.
 * User: michael1
 * Date: 2015-05-22
 * Time: 3:13 PM
 */
class Endpoint
{
    const PROTOCOL = "https";
    const API_HOST = "api.vineapp.com";
    const MEDIA_HOST = "media.vineapp.com";

    /** @const */
    static $HEADERS = [
                        'Host: api.vineapp.com',
                        'Proxy-Connection: keep-alive',
                        'Accept: */*',
                        'X-Vine-Client: ios/2.5.1',
                        'Accept-Language: en;q=1',
                        'Connection: keep-alive',
                        'User-Agent: iphone/172 (iPad; iOS 7.0.4; Scale/2.00)',
                        'Expect:' // Disable cURL's 100 continue
                    ];

    /** @const */
    static $ENDPOINTS = [
                // Auth
                'login'=> [
                    'endpoint'=> 'users/authenticate',
                    'request_type'=> 'post',
                    'url_params'=> [],
                    'required_params'=> ['username', 'password'],
                    'optional_params'=> ['deviceToken'],
                    'default_params'=> [],
                    'model'=> User::class
                ],
                'logout'=> [
                    'endpoint'=> 'users/authenticate',
                    'request_type'=> 'delete',
                    'url_params'=> [],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'signup'=> [
                    'endpoint'=> 'users',
                    'request_type'=> 'post',
                    'url_params'=> [],
                    'required_params'=> ['email', 'password', 'username'],
                    'optional_params'=> [],
                    'default_params'=> [
                        ['authenticate'=> 1]
                    ],
                    'model'=> User::class
                ],
                
                // Profile
                'get_me'=> [
                    'endpoint'=> 'users/me',
                    'request_type'=> 'get',
                    'url_params'=> [],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> User::class
                ],
                'get_user'=> [
                    'endpoint'=> 'users/profiles/%s',
                    'request_type'=> 'get',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> User::class
                ],
                'update_profile'=> [
                    'endpoint'=> 'users/%s',
                    'request_type'=> 'put',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> ['username', 'description', 'location', 'locale', 'email', 'private', 'phoneNumber', 'avatarUrl', 'profileBackground', 'acceptsOutOfNetworkConversations'],
                    'model'=> null
                ],
                'set_explicit'=> [
                    'endpoint'=> 'users/%s/explicit',
                    'request_type'=> 'post',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'unset_explicit'=> [
                    'endpoint'=> 'users/%s/explicit',
                    'request_type'=> 'delete',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                
                // User actions
                'follow'=> [
                    'endpoint'=> 'users/%s/followers',
                    'request_type'=> 'post',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> ['notify'], # notify=1 to follow notifications as well
                    'model'=> null
                ],
                'follow_notifications'=> [
                    'endpoint'=> 'users/%s/followers/notifications',
                    'request_type'=> 'post',
                    'url_params'=> ['user_id'],
                    'required_params'=> ['notify'],
                    'optional_params'=> [],
                    'default_params'=> [
                        [ 'notify' => 1 ]
                    ],
                    'model'=> null
                ],
                'unfollow'=> [
                    'endpoint'=> 'users/%s/followers',
                    'request_type'=> 'delete',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'unfollow_notifications'=> [
                    'endpoint'=> 'users/%s/followers/notifications',
                    'request_type'=> 'delete',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'block'=> [
                    'endpoint'=> 'users/%s/blocked/%s',
                    'request_type'=> 'post',
                    'url_params'=> ['from_user_id', 'to_user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'unblock'=> [
                    'endpoint'=> 'users/%s/blocked/%s',
                    'request_type'=> 'delete',
                    'url_params'=> ['from_user_id', 'to_user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'get_pending_notifications_count'=> [
                    'endpoint'=> 'users/%s/pendingNotificationsCount',
                    'request_type'=> 'get',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'get_notifications'=> [
                    'endpoint'=> 'users/%s/notifications',
                    'request_type'=> 'get',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> NotificationCollection::class
                ],
            
                // User lists
                'get_followers'=> [
                    'endpoint'=> 'users/%s/followers',
                    'request_type'=> 'get',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> UserCollection::class
                ],
                'get_following'=> [
                    'endpoint'=> 'users/%s/following',
                    'request_type'=> 'get',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> UserCollection::class
                ],
            
                'get_conversations'=> [
                    'endpoint'=> 'users/%s/conversations',
                    'request_type'=> 'get',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> ['inbox'],
                    'model'=> ConversationCollection::class
                ],
            
            
                'start_conversation'=> [
                    'endpoint'=> 'conversations',
                    'request_type'=> 'post',
                    'json'=> true,
                    'url_params'=> [],
                    'required_params'=> ['created', 'locale', 'message', 'to'],
                    'optional_params'=> [],
                    'model'=> MessageCollection::class
                ],
            
                'converse'=> [
                    'endpoint'=> 'conversations/%s',
                    'request_type'=> 'post',
                    'json'=> true,
                    'url_params'=> ['conversation_id'],
                    'required_params'=> ['created', 'locale', 'message'],
                    'optional_params'=> [],
                    'model'=> MessageCollection::class
                ],
                
                // Posts actions
                'like'=> [
                    'endpoint'=> 'posts/%s/likes',
                    'request_type'=> 'post',
                    'url_params'=> ['post_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> Like::class
                ],
                'unlike'=> [
                    'endpoint'=> 'posts/%s/likes',
                    'request_type'=> 'delete',
                    'url_params'=> ['post_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'comment'=> [
                    'endpoint'=> 'posts/%s/comments',
                    'request_type'=> 'post',
                    'json'=> true,
                    'url_params'=> ['post_id'],
                    'required_params'=> ['comment', 'entities'],
                    'optional_params'=> [],
                    'model'=> Comment::class
                ],
                'uncomment'=> [
                    'endpoint'=> 'posts/%s/comments/%s',
                    'request_type'=> 'delete',
                    'url_params'=> ['post_id', 'comment_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'revine'=> [
                    'endpoint'=> 'posts/%s/repost',
                    'request_type'=> 'post',
                    'url_params'=> ['post_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> Repost::class
                ],
                'unrevine'=> [
                    'endpoint'=> 'posts/%s/repost/%s',
                    'request_type'=> 'delete',
                    'url_params'=> ['post_id', 'revine_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'report'=> [
                    'endpoint'=> 'posts/%s/complaints',
                    'request_type'=> 'post',
                    'url_params'=> [],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'post'=> [
                    'endpoint'=> 'posts',
                    'request_type'=> 'post',
                    'json'=> true,
                    'url_params'=> [],
                    'required_params'=> ['videoUrl', 'thumbnailUrl', 'description', 'entities'],
                    'optional_params'=> ['forsquareVenueId', 'venueName'],
                    'default_params'=> [
                        ['channelId' => '0']
                    ],
                    'model'=> Post::class
                ],
                'delete_post'=> [
                    'endpoint'=> 'posts/%s',
                    'request_type'=> 'delete',
                    'url_params'=> ['post_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'get_post_likes'=> [
                    'endpoint'=> 'posts/%s/likes',
                    'request_type'=> 'get',
                    'url_params'=> ['post_id'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> LikeCollection::class
                ],
                'get_post_comments'=> [
                    'endpoint'=> 'posts/%s/comments',
                    'request_type'=> 'get',
                    'url_params'=> ['post_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> CommentCollection::class
                ],
                'get_post_reposts'=> [
                    'endpoint'=> 'posts/%s/reposts',
                    'request_type'=> 'get',
                    'url_params'=> ['post_id'],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> RepostCollection::class
                ],
                
                
                // Timelines
                'get_post'=> [
                    'endpoint'=> 'timelines/posts/%s',
                    'request_type'=> 'get',
                    'url_params'=> ['post_id'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_user_timeline'=> [
                    'endpoint'=> 'timelines/users/%s',
                    'request_type'=> 'get',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_user_likes'=> [
                    'endpoint'=> 'timelines/users/%s/likes',
                    'request_type'=> 'get',
                    'url_params'=> ['user_id'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_tag_timeline'=> [
                    'endpoint'=> 'timelines/tags/%s',
                    'request_type'=> 'get',
                    'url_params'=> ['tag_name'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_graph_timeline'=> [
                    'endpoint'=> 'timelines/graph',
                    'request_type'=> 'get',
                    'url_params'=> [],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_popular_timeline'=> [
                    'endpoint'=> 'timelines/popular',
                    'request_type'=> 'get',
                    'url_params'=> [],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_promoted_timeline'=> [
                    'endpoint'=> 'timelines/promoted',
                    'request_type'=> 'get',
                    'url_params'=> [],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_channel_popular_timeline'=> [
                    'endpoint'=> 'timelines/channels/%s/popular',
                    'request_type'=> 'get',
                    'url_params'=> ['channel_id'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_channel_recent_timeline'=> [
                    'endpoint'=> 'timelines/channels/%s/recent',
                    'request_type'=> 'get',
                    'url_params'=> ['channel_id'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
                'get_venue_timeline'=> [
                    'endpoint'=> 'timelines/venues/%s',
                    'request_type'=> 'get',
                    'url_params'=> ['venue_id'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> PostCollection::class
                ],
            
                // Tags
                'get_trending_tags'=> [
                    'endpoint'=> 'tags/trending',
                    'request_type'=> 'get',
                    'url_params'=> [],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> TagCollection::class
                ],
                
                // Channels
                'get_featured_channels'=> [
                    'endpoint'=> 'channels/featured',
                    'request_type'=> 'get',
                    'url_params'=> [],
                    'required_params'=> [],
                    'optional_params'=> [],
                    'model'=> ChannelCollection::class
                ],
                
                # Search
                'search_tags'=> [
                'endpoint'=> 'tags/search/%s',
                    'request_type'=> 'get',
                    'url_params'=> ['tag_name'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> TagCollection::class
                ],
                'search_users'=> [
                    'endpoint'=> 'users/search/%s',
                    'request_type'=> 'get',
                    'url_params'=> ['user_name'],
                    'required_params'=> [],
                    'optional_params'=> ['size', 'page', 'anchor'],
                    'model'=> UserCollection::class
                ],
            
                // Upload Media
                'upload_avatar'=> [
                    'host'=> self::MEDIA_HOST,
                    'endpoint'=> 'upload/avatars/1.3.1.jpg',
                    'request_type'=> 'put',
                    'url_params'=> [],
                    'required_params'=> ['filename'],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'upload_thumb'=> [
                    'host'=> self::MEDIA_HOST,
                    'endpoint'=> 'upload/thumbs/2.5.1.15482401929932289311.mp4.jpg?private=1',
                    'request_type'=> 'put',
                    'url_params'=> [],
                    'required_params'=> ['filename'],
                    'optional_params'=> [],
                    'model'=> null
                ],
                'upload_video'=> [
                    'host'=> self::MEDIA_HOST,
                    'endpoint'=> 'upload/videos/2.5.1.15482401929932289311.mp4?private=1',
                    'request_type'=> 'put',
                    'url_params'=> [],
                    'required_params'=> ['filename'],
                    'optional_params'=> [],
                    'model'=> null
                ]
            ];
}