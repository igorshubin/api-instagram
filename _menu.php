
<div class="btn-group">
    <a href="/popular.php" class="btn btn-danger small">Popular</a>
</div>

<div class="btn-group">
    <a href="/profile.php" class="btn btn-success small">Profile</a>
</div>

<div class="btn-group">
    <a href="#" data-toggle="dropdown" class="btn btn-info small dropdown-toggle">
        Feeds
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        <li><a href="/feed.php?sort=date">Feeds By Date</a></li>
        <li><a href="/feed.php?sort=likes">Feeds By Likes</a></li>
    </ul>   
</div>

<div class="btn-group">
    <a href="#" data-toggle="dropdown" class="btn btn-warning small dropdown-toggle">
        Search
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        <li><a href="/search_tag.php">Search By Tag</a></li>
        <li><a href="/search_user.php">Search By User</a></li>
        <li><a href="/search_location.php">Search By Location</a></li>
    </ul>   
</div>      

<div class="btn-group">
    <a href="#" data-toggle="dropdown" class="btn btn-inverse small dropdown-toggle">
        Follows
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu">
        <li><a href="/follow.php?type=FollowedBy">User Followed By</a></li>
        <li><a href="/follow.php?type=Follows">User Follows</a></li>
    </ul>   
</div>   


<a href="/logout.php" class="btn small pull-right" style="margin-right: 5px;">Logout</a> 
