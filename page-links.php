<ol>
<?php

global $post;

//Write Site URL below.
//Don't write http:// or anything like that. just domain.com or domain.net
$_site_url = 'gauravtiwari.org';

$args = array(
    'posts_per_page'   => 9999999999,
    'offset'           => 0,
    'category'         => '',
    'category_name'    => '',
    'orderby'          => 'ID',
    'order'            => 'DESC',
    'include'          => '',
    'exclude'          => '',
    'meta_key'         => '',
    'meta_value'       => '',
    'post_type'        => 'post',
    'post_mime_type'   => '',
    'post_parent'      => '',
    'author'       => '',
    'author_name'      => '',
    'post_status'      => 'publish',
    'suppress_filters' => true
);
$myposts = get_posts( $args );
foreach( $myposts as $post ) : setup_postdata($post);

  //Get Post content
  $_post_content =  get_the_content();

  $site_parts = explode('.',$_site_url);
  $site_suffix = '.'.$site_parts[1];

  //Using regular expression to match hyperlink
  preg_match_all('|<a.*(?=href=\"([^\"]*)\")[^>]*>([^<]*)</a>|i', $_post_content, $match);

  foreach($match[0] as $link){
    //Filtering out internal links
    $parts = explode($site_suffix, $link);
    $domain = explode('//',$parts[0]);
    //echo $domain[1];
    if ($domain[1] != 'www.'.$site_parts[0] && $domain[1] != $site_parts[0] && strpos($link, 'nofollow') === FALSE){
        echo '<li>'. $link.'</li>';
    }
  }

endforeach;
wp_reset_postdata();

?>
</ol>
