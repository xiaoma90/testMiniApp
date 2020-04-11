<?php
    return [
        'plugin_arr' => [
            'ali'  => 'alismallprogram_0513', //支付宝
            'byte' => '85134#bytedancewu134', //字节跳动
            'qq'   => 'zhou$qqminiprogram15', //QQ
            'pc'   => 'pc@wangzhan_06199405', //pc
            'h5'   => 'shao#html515312911+0', //h5
            'ms'   => 'seconds+buygoods_528', //秒杀
            'pt'   => 'manypeoplespellgroup', //拼团
            'yu'   => '1531appointment$$che', //预约预定
            'ba'   => 'bart++eri886nggoods%', //砍价
            'yy'   => '19shakelotterydraw_w', //摇一摇
            'jf'   => 'integralsign#6513_az', //积分签到
            'dh'   => 'r*zsintegralexchange', //积分兑换
            'ds'   => 'mul_shops#ruiz*17lol', //多商户
            'cy'   => '*pongrestaurantry13#', //餐饮
            'fm'   => 'king99cityforumnet*1', //微同城
            'sp'   => 'supplydemand#imyddko', //供求关系
            'at'   => 'lsg*enrollment#raven', //活动报名
        ],
        'rule_id' => [
            'ms' => [77,78,79,80,81],
            'pt' => [87,88,89,90,91,92,93],
            'yu' => [82,83,84,85,86],
            'ba' => [134,135,136,137,138,139],
            'yy' => [130,131,132],
            'jf' => [111,112,113],
            'dh' => [114,115,116,117],
            'ds' => [94,95,96,97,98,99,100,101,102],
            'cy' => [103,104,105,106,107,108],
            'fm' => [121,122,123,124,125],
            'sp' => [126,127,128,129],
            'at' => [201,202,203]
        ],
        'plugin_route' => [
            'ali' => 'index/downloadAli, index/aliset',
            'byte' => 'index/downloadBdance, index/bdanceset',
            'qq' => 'index/downloadqq, index/qqset',
            'h5' => 'index/h5set',
            'pc' => 'index/pcset',
            'ms' => 'flashsale/catelist,flashsale/add,flashsale/del,flashsale/delallcate,flashsale/save,flashsale/delallm,flashsale/pro,flashsale/addpro,flashsale/savepro,flashsale/delpro,flashsale/orders,flashsale/baseset,flashsale/setsave',
            'pt' => 'pt/set,pt/setsave,pt/cate,pt/cateadd,pt/catesave,pt/catedel,pt/pro,pt/proadd,pt/prosave,pt/delall,pt/prodel,pt/qxorder,pt/order,pt/yaoqing,pt/tuikuan',
            'yu' => 'reserve/catelist,reserve/add,reserve/save,reserve/pro,reserve/addpro,reserve/del,reserve/savePro,reserve/delallpro,reserve/delpro,reserve/orderqx,reserve/orders,reserve/excel,reserve/orderhx,reserve/orderqr,reserver/changedate,reserve/refusemodify,reserve/acceptmodify,reserve/ordernqx,reserve/set,reserve/setsave',
            'ba' => 'bargain/set,bargain/saveset,bargain/cate,bargain/addcate,bargain/savecate,bargain/delcate,bargain/delcate,bargain/prolist,bargain/addpro,bargain/delpro,bargain/savepro,bargain/bargain,bargain/orderlist,bargain/excel',
            'yy' => 'shake/getprize,shake/index,shake/add,shake/setsave,shake/setprize,shake/record,shake/edit,shake/delactivity,Shake/changemeans,shake/deleteprize,shake/getprize,shake/selectprize,shake/addprize,shake/setedit,shake/shenhe,shake/changemeans,shake/delprize,shake/jfrule,shake/addrule,shake/saverule,shake/editrule,shake/save_editrule,shake/delrule',
            'jf' => 'sign/set,sign/save,sign/lists',
            'dh' => 'exchangescore/catelist,exchangescore/cateadd,exchangescore/catesave,exchangescore/catedel,exchangescore/goodslist,exchangescore/goodsadd,exchangescore/goodssave,exchangescore/goodsdel,exchangescore/orderlist,exchangescore/hx',
            'ds' => 'powerfulsh/system,powerfulsh/systemsave,powerfulsh/cate,powerfulsh/cateadd,powerfulsh/catesave,powerfulsh/catedel,powerfulsh/goodscate,powerfulsh/goodscateadd,powerfulsh/goodscatesave,powerfulsh/goodscatedel,powerfulsh/tenant,powerfulsh/tenantadd,powerfulsh/tenantdel,powerfulsh/qrcode,powerfulsh/tenantsave,powerfulsh/downloadimg,powerfulsh/goods,powerfulsh/goodsadd,powerfulsh/goodssave,powerfulsh/goodsdel,powerfulsh/goodspass,powerfulsh/goodscancel,powerfulsh/goodsdel,powerfulsh/order,powerfulsh/orderdown,powerfulsh/withdraw,Powerfulsh/withdrawpass,powerfulsh/shoppay',
            'cy' => 'cyindex/index,cyindex/save,cycate/index,cycate/add,cycate/save,cycate/del,cytablenum/index,cytablenum/add,cytablenum/save,cytablenum/del,cygoods/index,cygoods/add,cygoods/save,cygoods/del,cyorder/index,cyorder/orderdown',
            'fm' => 'forum/func,forum/funcAdd,forum/funcsave,forum/funcdel,forum/checktitle,forum/release,forum/releasehot,forum/releasecon,forum/releasedel,forum/releaseshenhe,forum/comment,forum/commentdel,forum/set,forum/setsave',
            'sp' => 'supply/release,supply/releasecon,supply/releasehot,supply/releasedel,supply/releaseshenhe,supply/comment,forum/commentdel,supply/set,supply/setsave',
            'at' => 'active/catelist,active/cateadd,active/catedel,,active/catesave,active/delall,active/listsadd,active/listsdel,active/delallactive,active/listssave,active/applylist,active/shenhe,active/applyinfo,active/applylist,active/excel,'
        ]
    ];