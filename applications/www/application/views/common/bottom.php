<?php if($userInfo['person_type'] == C('user.user_type.code.passenger')):?>
<div class="weui_tabbar">
    <a href="/home" class="weui_tabbar_item <?php if(isset($nav_tag) && $nav_tag == 'home'):?> weui_bar_item_on <?php endif;?>">
        <div class="weui_tabbar_icon">
            <img src="<?php echo $domain['static']['url']?>/app/images/icon_nav_button.png" alt="">
        </div>
        <p class="weui_tabbar_label">约车首页</p>
    </a>
    <a href="/usercenter" class="weui_tabbar_item <?php if(isset($nav_tag) && $nav_tag == 'usercenter'):?> weui_bar_item_on <?php endif;?>">
        <div class="weui_tabbar_icon">
            <img src="<?php echo $domain['static']['url']?>/app/images/icon_nav_article.png" alt="">
        </div>
        <p class="weui_tabbar_label">我的订单</p>
    </a>
    <a href="/usercenter/user_my_line" class="weui_tabbar_item <?php if(isset($nav_tag) && $nav_tag == 'user_my_line'):?> weui_bar_item_on <?php endif;?>" style="display:none">
        <div class="weui_tabbar_icon">
            <img src="<?php echo $domain['static']['url']?>/app/images/icon_nav_cell.png" alt="">
        </div>
        <p class="weui_tabbar_label">我的线路</p>
    </a>
    <a href="/Usercenter/info" class="weui_tabbar_item <?php if(isset($nav_tag) && $nav_tag == 'user_info'):?> weui_bar_item_on <?php endif;?>">
        <div class="weui_tabbar_icon">
            <img src="<?php echo $domain['static']['url']?>/app/images/info_logo.png" alt="">
        </div>
        <p class="weui_tabbar_label">个人信息</p>
    </a>
</div>
<?php else:?>
<div class="weui_tabbar">
    <a href="/home/driver" class="weui_tabbar_item <?php if(isset($nav_tag) && $nav_tag == 'home'):?> weui_bar_item_on <?php endif;?>">
        <div class="weui_tabbar_icon">
            <img src="<?php echo $domain['static']['url']?>/app/images/icon_nav_button.png" alt="">
        </div>
        <p class="weui_tabbar_label">抢单首页</p>
    </a>
    <a href="/driver/orders" class="weui_tabbar_item <?php if(isset($nav_tag) && $nav_tag == 'driver_order'):?> weui_bar_item_on <?php endif;?>">
        <div class="weui_tabbar_icon">
            <img src="<?php echo $domain['static']['url']?>/app/images/icon_nav_article.png" alt="">
        </div>
        <p class="weui_tabbar_label">我的订单</p>
    </a>
    <a href="/driver/road" class="weui_tabbar_item <?php if(isset($nav_tag) && $nav_tag == 'driver_road'):?> weui_bar_item_on <?php endif;?>" style="display:none">
        <div class="weui_tabbar_icon">
            <img src="<?php echo $domain['static']['url']?>/app/images/icon_nav_cell.png" alt="">
        </div>
        <p class="weui_tabbar_label">我的线路</p>
    </a>
    <a href="/driver/info" class="weui_tabbar_item <?php if(isset($nav_tag) && $nav_tag == 'driver_info'):?> weui_bar_item_on <?php endif;?>">
        <div class="weui_tabbar_icon">
            <img src="<?php echo $domain['static']['url']?>/app/images/info_logo.png" alt="">
        </div>
        <p class="weui_tabbar_label">个人信息</p>
    </a>
</div>
<?php endif;?>