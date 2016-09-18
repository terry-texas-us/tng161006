<?php
$query = "DELETE from branches WHERE branch = '$branch'";
$result = tng_query($query);

$query = "DELETE from branchlinks WHERE branch = '$branch'";
$result = tng_query($query);

$query = "UPDATE $people_table SET branch=\"\" WHERE branch = '$branch'";
$result = tng_query($query);

$query = "UPDATE $people_table SET branch=REPLACE(branch,\"$branch,\",\"\") WHERE branch LIKE \"$branch,%\"";
$result = tng_query($query);

$query = "UPDATE $people_table SET branch=REPLACE(branch,\",$branch\",\"\") WHERE branch LIKE \"%,$branch\"";
$result = tng_query($query);

$query = "UPDATE $people_table SET branch=REPLACE(branch,\",$branch,\",\",\") WHERE branch LIKE \"%,$branch,%\"";
$result = tng_query($query);

$query = "UPDATE families SET branch=\"\" WHERE branch = \"$branch\"";
$result = tng_query($query);

$query = "UPDATE families SET branch=REPLACE(branch,\"$branch,\",\"\") WHERE branch LIKE \"$branch,%\"";
$result = tng_query($query);

$query = "UPDATE families SET branch=REPLACE(branch,\",$branch\",\"\") WHERE branch LIKE \"%,$branch\"";
$result = tng_query($query);

$query = "UPDATE families SET branch=REPLACE(branch,\",$branch,\",\",\") WHERE branch LIKE \"%,$branch,%\"";
$result = tng_query($query);
