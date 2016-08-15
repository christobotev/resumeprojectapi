<?php
namespace Docs\MainBundle\Controller\User;

use Docs\MainBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RolesController extends AbstractController
{

    protected $entityClass = "\Docs\CommonBundle\Entity\Role";

    public function getRolesAction(Request $request)
    {
        $inGroup = $request->get("inGroup", null);

        // if the request is for groups of a user,
        // filter it according to the roles of the user with the given cas id
        if ($inGroup !== null) {
            $casID = $request->get("parent") ? : $request->get("casID");

            $userRepo = $this->getEntityManager()
                                ->getRepository("\Docs\CommonBundle\Entity\User");

            $user = $userRepo->findOneBy(["casID" => $casID]);

            if (empty($user)) {
                return $this->returnHBResponse(["result" => ["count" => 0]], 200);
            }

            $userRolesRepo = $this->getEntityManager()
                                    ->getRepository("\Docs\CommonBundle\Entity\UserRoles");

            $userRoles = $userRolesRepo->findBy(["user" => $user->getUserID()]);

            $givenRoles = [];
            $roles = [];
            foreach ($userRoles as $userRole) {
                $givenRoles[] = $userRole->getRole()->getRoleID();
                $roles[] = $userRole->getRole();
            }

            if ($inGroup == 1) {
                $roles['count'] = count($roles);
                return $this->returnHBResponse(["result" => $roles]);
            }

            if (count($givenRoles)) {
                $rolesRepo = $this->getEntityManager()
                                    ->getRepository("\Docs\CommonBundle\Entity\Role");
                /* @var $rolesRepo \Docs\CommonBundle\Repository\RoleRepository */

                $rolesRepo->addWhere("roleID", $givenRoles, "NOTIN");
            }
        }

        return $this->listAll();
    }

    public function getRoleAction($roleID)
    {
        return $this->findByID($roleID);
    }
}
