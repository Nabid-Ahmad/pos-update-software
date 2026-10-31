<?php


    use App\Models\Permission\Permission;
    use Facades\App\Repositories\PermissionCache;
    

    function console_log($output)
    {
        echo "<script> console.log(" . json_encode($output, JSON_HEX_TAG) . "); </script>";
    }
    function console_table($output) {
        echo "<script> console.table(" . json_encode($output, JSON_HEX_TAG) . "); </script>";
    }


    function isSuperAdmin()
    {
        if (!auth()->check()) {
            return false;
        }
        $user = auth()->user();
        $role_id = optional($user->user_role)->role_id;

        return ($user->id == 1 || $user->email == 'admin@gmail.com' || $user->email == 'superadmin@gmail.com' || $role_id == 1);
    }

    function hasPermission ($slug)
    {
        if (isSuperAdmin()) {
            return true;
        }

        if (is_array($slug)) {
            if(count(array_intersect(PermissionCache::allPermittedSlugs(), $slug)) !== 0) {
                return true;
            }
            return false;
        }
        return in_array($slug, PermissionCache::allPermittedSlugs());
    }




        // <!-- check user has the permission  -->
        function checkIsPermitted()
        {
            $routeName = Request::route()->getName();
            if (!isSuperAdmin() && $routeName != null) { // not super admin and route name defined
                if (in_array($routeName, PermissionCache::allSlugs())) {   // is create any permission for this route
                    if (!in_array($routeName, PermissionCache::allPermittedSlugs())) {
                        return false;
                    }
                }
            }
            return true;
        }



