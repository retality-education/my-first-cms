<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
	  
    <h1>Users</h1>
	  
    <?php if ( isset( $results['errorMessage'] ) ) { ?>
        <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>
	  
    <?php if ( isset( $results['statusMessage'] ) ) { ?>
        <div class="statusMessage"><?php echo $results['statusMessage'] ?></div>
    <?php } ?>
	  
    <table>
        <tr>
            <th>Username</th>
            <th>Status</th>
        </tr>

        <?php foreach ( $results['users'] as $user ) { ?>
            <tr onclick="location='admin.php?action=editUser&amp;userId=<?php echo $user->id?>'">
                <td>
                    <?php echo $user->username ?>
                </td>
                <td>
                    <?php echo $user->activity == 1 ? 'Активен' : 'Не активен'; ?>
                </td>
            </tr>
        <?php } ?>

    </table>

    <p><?php echo $results['totalRows']?> user<?php echo ( $results['totalRows'] != 1 ) ? 's' : '' ?> in total.</p>

    <p><a href="admin.php?action=newUser">Add a New User</a></p>
	  
<?php include "templates/include/footer.php" ?>