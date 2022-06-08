## 获取米游社Cookie

1. 打开你的浏览器,进入**无痕/隐身模式**

2. 由于米哈游修改了bbs可以获取的Cookie，导致一次获取的Cookie缺失，所以需要增加步骤

3. 打开`http://bbs.mihoyo.com/ys/`并进行登入操作

4. 在上一步登入完成后新建标签页，打开`http://user.mihoyo.com/`并进行登入操作 (如果你不需要自动获取米游币可以忽略这个步骤，并把`mihoyobbs`的`enable`改为`false`即可)

5. 按下键盘上的`F12`或右键检查,打开开发者工具,点击Console

6. 输入

   ```javascript
   var cookie=document.cookie;var ask=confirm('Cookie:'+cookie+'\n\nDo you want to copy the cookie to the clipboard?');if(ask==true){copy(cookie);msg=cookie}else{msg='Cancel'}
   ```

   回车执行，并在确认无误后点击确定。

7. **此时Cookie已经复制到你的粘贴板上了**

current_resin=35 当前树脂
    max_resin=160 树脂上限
    resin_recovery_time=59900 树脂恢复时间
    remain_resin_discount_num=3 本周剩余树脂减半次数
    resin_discount_num_limit=3 本周树脂减半次数上限

    current_expedition_num=5 当前派遣数量
    max_expedition_num=5 最大派遣数量
    finished_task_num=0 完成的委托数量
    total_task_num=4 全部委托数量
    is_extra_task_reward_received=False 每日委托奖励是否领取
    current_home_coin: 当前洞天宝钱数量
    max_home_coin: 洞天宝钱存储上限
    home_coin_recovery_time: 洞天宝钱溢出时间
