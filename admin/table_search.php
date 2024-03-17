<?php if (!empty($admins)) : ?>
   <?php foreach ($admins as $admin) : ?>
      <?php
      $status = $admin['status'];
      // Kiểm tra vai trò
      switch ($status) {
         case "block":
            $sta = "Tài khoản bị khóa";
            break;
         default:
            $sta = "Hoạt động";
      }
      ?>
      <tr>
         <td><?= $admin['user_id'] ?></td>
         <td><?= $admin['name'] ?></td>
         <td><?= $admin['email'] ?></td>
         <td><?= $admin['number'] ?></td>
         <td><?= $admin['address'] ?></td>
         <td><a href="users_accounts.php?update=<?= $admin['user_id']; ?>" class="update-btn" onclick="return confirm('Bạn muốn thay đổi trạng thái tài khoản này ');"><?= $sta ?></a></td>
         <td>
            edit
            /
            <a href="users_accounts.php?delete=<?= $admin['user_id']; ?>" class="delete-bt" onclick="return confirm('bạn muốn xóa tài khoản này ?');">Delete</a>
         </td>
      </tr>
   <?php endforeach; ?>
<?php else : ?>
   <tr>
      <td colspan="7">Không có dữ liệu người dùng.</td>
   </tr>
<?php endif; ?>