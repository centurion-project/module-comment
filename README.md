Module Comment


# Description

This module allow you to add comment where you want.

# How to use it

Put this code in the view where you want to see comment:

    echo $this->action('get', 'wall', 'comment', array('proxy' => $proxyRow));


$proxyRow should be a row. All comment will be ratached to him.

# TOOD

- add options managment
- manage unlogued version if option allow to post comment without being register
- add test unit
- give a default css
