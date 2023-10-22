<?php
include "session.php"; include "functions.php";
if ((!$rPermissions["is_admin"]) OR (!hasPermissions("adv", "categories"))) { exit; }

if (isset($_POST["categories"])) {
    $rPostCategories = json_decode($_POST["categories"], True);
    if (count($rPostCategories) > 0) {
        foreach ($rPostCategories as $rOrder => $rPostCategory) {
            $db->query("UPDATE `stream_categories` SET `cat_order` = ".(intval($rOrder)+1).", `parent_id` = 0 WHERE `id` = ".intval($rPostCategory["id"]).";");
            if (isset($rPostCategory["children"])) {
                foreach ($rPostCategory["children"] as $rChildOrder => $rChildCategory) {
                    $db->query("UPDATE `stream_categories` SET `cat_order` = ".(intval($rChildOrder)+1).", `parent_id` = ".intval($rPostCategory["id"])." WHERE `id` = ".intval($rChildCategory["id"]).";");
                }
            }
        }
    }
}

$rCategories = Array(1 => getCategories(), 2 => getCategories("movie"), 3 => getCategories("series"), 4 => getCategories("radio"));
$rMainCategories = Array(1 => Array(), 2 => Array(), 3 => Array()); $rSubCategories = Array(1 => Array(), 2 => Array(), 3 => Array(), 4 => Array());

foreach (Array(1,2,3,4) as $rID) {
    foreach ($rCategories[$rID] as $rCategoryID => $rCategoryData) {
        if ($rCategoryData["parent_id"] <> 0) {
            $rSubCategories[$rID][$rCategoryData["parent_id"]][] = $rCategoryData;
        } else {
            $rMainCategories[$rID][] = $rCategoryData;
        }
    }
}

if ($rSettings["sidebar"]) {
    include "header_sidebar.php";
} else {
    include "header.php";
}
        if ($rSettings["sidebar"]) { ?>
        <div class="content-page"><div class="content boxed-layout"><div class="container-fluid">
        <?php } else { ?>
        <div class="wrapper boxed-layout"><div class="container-fluid">
        <?php } ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box">
							<?php if (hasPermissions("adv", "add_cat")) { ?>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li>
                                        <a href="stream_category.php">
                                            <button type="button" class="btn btn-success waves-effect waves-light btn-sm">
                                                <i class="mdi mdi-plus"></i> Add Category
                                            </button>
                                        </a>
                                    </li>
                                </ol>
                            </div>
							<?php } ?>
                            <h4 class="page-title">Categories</h4>
                        </div>
                    </div>
                </div>     
                <!-- end page title --> 
                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <div id="basicwizard">
                                    <ul class="nav nav-pills bg-light nav-justified form-wizard-header mb-4">
                                        <li class="nav-item">
                                            <a href="#category-order-1" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                <i class="mdi mdi-play mr-1"></i>
                                                <span class="d-none d-sm-inline">Streams</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#category-order-2" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                <i class="mdi mdi-movie mr-1"></i>
                                                <span class="d-none d-sm-inline">Movies</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#category-order-3" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                <i class="mdi mdi-youtube-tv mr-1"></i>
                                                <span class="d-none d-sm-inline">Series</span>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#category-order-4" data-toggle="tab" class="nav-link rounded-0 pt-2 pb-2"> 
                                                <i class="mdi mdi-radio mr-1"></i>
                                                <span class="d-none d-sm-inline">Radio</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content b-0 mb-0 pt-0">
                                        <div class="tab-pane" id="category-order-1">
                                            <form action="./stream_categories.php" method="POST" id="stream_categories_form-1">
                                                <input type="hidden" id="categories_input-1" name="categories" value="" />
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            To re-order a category, drag it up or down the list using the <i class="mdi mdi-view-sequential"></i> icon. Click Save Changes at the bottom once finished.
                                                        </p>
                                                        <div class="custom-dd dd" id="category_order-1">
                                                            <ol class="dd-list">
                                                                <?php foreach ($rMainCategories[1] as $rCategory) { ?>
                                                                <li class="dd-item dd3-item category-<?=$rCategory["id"]?>" data-id="<?=$rCategory["id"]?>">
                                                                    <div class="dd-handle dd3-handle"></div>
                                                                    <div class="dd3-content"><?=$rCategory["category_name"]?>
                                                                        <span style="float:right;">
																			<?php if (hasPermissions("adv", "edit_cat")) { ?>
                                                                            <div class="btn-group">
                                                                                <a href="./stream_category.php?id=<?=$rCategory["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                                                <button type="button" class="btn btn-light waves-effect waves-light" onClick="deleteCategory(<?=$rCategory["id"]?>)"><i class="mdi mdi-close"></i></button>
                                                                            </div>
																			<?php } ?>
                                                                        </span>
                                                                    </div>
                                                                    <?php if (isset($rSubCategories[1][$rCategory["id"]])) { ?>
                                                                    <ol class="dd-list">
                                                                        <?php foreach ($rSubCategories[1][$rCategory["id"]] as $rSubCategory) { ?>
                                                                        <li class="dd-item dd3-item category-<?=$rSubCategory["id"]?>" data-id="<?=$rSubCategory["id"]?>">
                                                                            <div class="dd-handle dd3-handle"></div>
                                                                            <div class="dd3-content"><?=$rSubCategory["category_name"]?>
                                                                                <span style="float:right;">
																					<?php if (hasPermissions("adv", "edit_cat")) { ?>
                                                                                    <div class="btn-group">
                                                                                        <a href="./stream_category.php?id=<?=$rSubCategory["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                                                        <button type="button" class="btn btn-light waves-effect waves-light" onClick="deleteCategory(<?=$rSubCategory["id"]?>)"><i class="mdi mdi-close"></i></button>
                                                                                    </div>
																					<?php } ?>
                                                                                </span>
                                                                            </div>
                                                                        </li>
                                                                        <?php } ?>
                                                                    </ol>
                                                                <?php } ?>
                                                                </li>
                                                                <?php } ?>
                                                            </ol>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="next list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Changes</button>
                                                    </li>
                                                </ul>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="category-order-2">
                                            <form action="./stream_categories.php" method="POST" id="stream_categories_form-2">
                                                <input type="hidden" id="categories_input-2" name="categories" value="" />
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            To re-order a category, drag it up or down the list using the <i class="mdi mdi-view-sequential"></i> icon. Click Save Changes at the bottom once finished.
                                                        </p>
                                                        <div class="custom-dd dd" id="category_order-2">
                                                            <ol class="dd-list">
                                                                <?php foreach ($rMainCategories[2] as $rCategory) { ?>
                                                                <li class="dd-item dd3-item category-<?=$rCategory["id"]?>" data-id="<?=$rCategory["id"]?>">
                                                                    <div class="dd-handle dd3-handle"></div>
                                                                    <div class="dd3-content"><?=$rCategory["category_name"]?>
                                                                        <span style="float:right;">
																			<?php if (hasPermissions("adv", "edit_cat")) { ?>
                                                                            <div class="btn-group">
                                                                                <a href="./stream_category.php?id=<?=$rCategory["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                                                <button type="button" class="btn btn-light waves-effect waves-light" onClick="deleteCategory(<?=$rCategory["id"]?>)"><i class="mdi mdi-close"></i></button>
																			</div>
                                                                            <?php } ?>
                                                                        </span>
                                                                    </div>
                                                                    <?php if (isset($rSubCategories[2][$rCategory["id"]])) { ?>
                                                                    <ol class="dd-list">
                                                                        <?php foreach ($rSubCategories[2][$rCategory["id"]] as $rSubCategory) { ?>
                                                                        <li class="dd-item dd3-item category-<?=$rSubCategory["id"]?>" data-id="<?=$rSubCategory["id"]?>">
                                                                            <div class="dd-handle dd3-handle"></div>
                                                                            <div class="dd3-content"><?=$rSubCategory["category_name"]?>
                                                                                <span style="float:right;">
																					<?php if (hasPermissions("adv", "edit_cat")) { ?>
                                                                                    <div class="btn-group">
                                                                                        <a href="./stream_category.php?id=<?=$rSubCategory["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                                                        <button type="button" class="btn btn-light waves-effect waves-light" onClick="deleteCategory(<?=$rSubCategory["id"]?>)"><i class="mdi mdi-close"></i></button>
																					</div>
                                                                                    <?php } ?>
                                                                                </span>
                                                                            </div>
                                                                        </li>
                                                                        <?php } ?>
                                                                    </ol>
                                                                <?php } ?>
                                                                </li>
                                                                <?php } ?>
                                                            </ol>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="next list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Changes</button>
                                                    </li>
                                                </ul>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="category-order-3">
                                            <form action="./stream_categories.php" method="POST" id="stream_categories_form-3">
                                                <input type="hidden" id="categories_input-3" name="categories" value="" />
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            To re-order a category, drag it up or down the list using the <i class="mdi mdi-view-sequential"></i> icon. Click Save Changes at the bottom once finished.
                                                        </p>
                                                        <div class="custom-dd dd" id="category_order-3">
                                                            <ol class="dd-list">
                                                                <?php foreach ($rMainCategories[3] as $rCategory) { ?>
                                                                <li class="dd-item dd3-item category-<?=$rCategory["id"]?>" data-id="<?=$rCategory["id"]?>">
                                                                    <div class="dd-handle dd3-handle"></div>
                                                                    <div class="dd3-content"><?=$rCategory["category_name"]?>
                                                                        <span style="float:right;">
																			<?php if (hasPermissions("adv", "edit_cat")) { ?>
                                                                            <div class="btn-group">
                                                                                <a href="./stream_category.php?id=<?=$rCategory["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                                                <button type="button" class="btn btn-light waves-effect waves-light" onClick="deleteCategory(<?=$rCategory["id"]?>)"><i class="mdi mdi-close"></i></button>
																			</div>
                                                                            <?php } ?>
                                                                        </span>
                                                                    </div>
                                                                    <?php if (isset($rSubCategories[3][$rCategory["id"]])) { ?>
                                                                    <ol class="dd-list">
                                                                        <?php foreach ($rSubCategories[3][$rCategory["id"]] as $rSubCategory) { ?>
                                                                        <li class="dd-item dd3-item category-<?=$rSubCategory["id"]?>" data-id="<?=$rSubCategory["id"]?>">
                                                                            <div class="dd-handle dd3-handle"></div>
                                                                            <div class="dd3-content"><?=$rSubCategory["category_name"]?>
                                                                                <span style="float:right;">
																					<?php if (hasPermissions("adv", "edit_cat")) { ?>
                                                                                    <div class="btn-group">
                                                                                        <a href="./stream_category.php?id=<?=$rSubCategory["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                                                        <button type="button" class="btn btn-light waves-effect waves-light" onClick="deleteCategory(<?=$rSubCategory["id"]?>)"><i class="mdi mdi-close"></i></button>
																					</div>
                                                                                    <?php } ?>
                                                                                </span>
                                                                            </div>
                                                                        </li>
                                                                        <?php } ?>
                                                                    </ol>
                                                                <?php } ?>
                                                                </li>
                                                                <?php } ?>
                                                            </ol>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="next list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Changes</button>
                                                    </li>
                                                </ul>
                                            </form>
                                        </div>
                                        <div class="tab-pane" id="category-order-4">
                                            <form action="./stream_categories.php" method="POST" id="stream_categories_form-4">
                                                <input type="hidden" id="categories_input-4" name="categories" value="" />
                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="sub-header">
                                                            To re-order a category, drag it up or down the list using the <i class="mdi mdi-view-sequential"></i> icon. Click Save Changes at the bottom once finished.
                                                        </p>
                                                        <div class="custom-dd dd" id="category_order-4">
                                                            <ol class="dd-list">
                                                                <?php foreach ($rMainCategories[4] as $rCategory) { ?>
                                                                <li class="dd-item dd3-item category-<?=$rCategory["id"]?>" data-id="<?=$rCategory["id"]?>">
                                                                    <div class="dd-handle dd3-handle"></div>
                                                                    <div class="dd3-content"><?=$rCategory["category_name"]?>
                                                                        <span style="float:right;">
																			<?php if (hasPermissions("adv", "edit_cat")) { ?>
                                                                            <div class="btn-group">
                                                                                <a href="./stream_category.php?id=<?=$rCategory["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                                                <button type="button" class="btn btn-light waves-effect waves-light" onClick="deleteCategory(<?=$rCategory["id"]?>)"><i class="mdi mdi-close"></i></button>
																			</div>
                                                                            <?php } ?>
                                                                        </span>
                                                                    </div>
                                                                    <?php if (isset($rSubCategories[4][$rCategory["id"]])) { ?>
                                                                    <ol class="dd-list">
                                                                        <?php foreach ($rSubCategories[4][$rCategory["id"]] as $rSubCategory) { ?>
                                                                        <li class="dd-item dd3-item category-<?=$rSubCategory["id"]?>" data-id="<?=$rSubCategory["id"]?>">
                                                                            <div class="dd-handle dd3-handle"></div>
                                                                            <div class="dd3-content"><?=$rSubCategory["category_name"]?>
                                                                                <span style="float:right;">
																					<?php if (hasPermissions("adv", "edit_cat")) { ?>
                                                                                    <div class="btn-group">
                                                                                        <a href="./stream_category.php?id=<?=$rSubCategory["id"]?>"><button type="button" class="btn btn-light waves-effect waves-light"><i class="mdi mdi-pencil-outline"></i></button></a>
                                                                                        <button type="button" class="btn btn-light waves-effect waves-light" onClick="deleteCategory(<?=$rSubCategory["id"]?>)"><i class="mdi mdi-close"></i></button>
																					</div>
                                                                                    <?php } ?>
                                                                                </span>
                                                                            </div>
                                                                        </li>
                                                                        <?php } ?>
                                                                    </ol>
                                                                <?php } ?>
                                                                </li>
                                                                <?php } ?>
                                                            </ol>
                                                        </div>
                                                    </div> <!-- end col -->
                                                </div> <!-- end row -->
                                                <ul class="list-inline wizard mb-0 add-margin-top-20">
                                                    <li class="next list-inline-item float-right">
                                                        <button type="submit" class="btn btn-primary waves-effect waves-light">Save Changes</button>
                                                    </li>
                                                </ul>
                                            </form>
                                        </div>
                                    </div>
                                </div> <!-- end #basicwizard-->
                            </div> <!-- end card-body -->
                        </div> <!-- end card-->
                    </div> <!-- end col -->
                </div>
            </div> <!-- end container -->
        </div>
        <!-- end wrapper -->
        <?php if ($rSettings["sidebar"]) { echo "</div>"; } ?>
        <!-- Footer Start -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 copyright text-center"><?=getFooter()?></div>
                </div>
            </div>
        </footer>
        <!-- end Footer -->

        <script src="assets/js/vendor.min.js"></script>
        <script src="assets/libs/jquery-toast/jquery.toast.min.js"></script>
        <script src="assets/libs/jquery-nice-select/jquery.nice-select.min.js"></script>
        <script src="assets/libs/switchery/switchery.min.js"></script>
        <script src="assets/libs/select2/select2.min.js"></script>
        <script src="assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
        <script src="assets/libs/clockpicker/bootstrap-clockpicker.min.js"></script>
        <script src="assets/libs/moment/moment.min.js"></script>
        <script src="assets/libs/daterangepicker/daterangepicker.js"></script>
        <script src="assets/libs/nestable2/jquery.nestable.min.js"></script>
        <script src="assets/libs/twitter-bootstrap-wizard/jquery.bootstrap.wizard.min.js"></script>
        <script src="assets/libs/treeview/jstree.min.js"></script>
        <script src="assets/js/pages/treeview.init.js"></script>
        <script src="assets/js/pages/form-wizard.init.js"></script>
        <script src="assets/js/app.min.js"></script>
        
        <script>
        function deleteCategory(rID) {
            if (confirm("Are you sure you want to delete this category? All streams attached will be uncategorised.")) {
                $.getJSON("./api.php?action=category&sub=delete&category_id=" + rID, function(data) {
                    if (data.result === true) {
                        $(".category-" + rID).remove();
                        $.toast("Category successfully deleted.");
                        $.each($('.tooltip'), function (index, element) {
                            $(this).remove();
                        });
                        $('[data-toggle="tooltip"]').tooltip();
                    } else {
                        $.toast("An error occured while processing your request.");
                    }
                });
            }
        }
        $(document).ready(function() {
            $("#category_order-1").nestable({maxDepth: 1});
            $("#category_order-2").nestable({maxDepth: 2});
            $("#category_order-3").nestable({maxDepth: 2});
            $("#category_order-4").nestable({maxDepth: 1});
            $("#stream_categories_form-1").submit(function(e){
                $("#categories_input-1").val(JSON.stringify($('#category_order-1.dd').nestable('serialize')));
            });
            $("#stream_categories_form-2").submit(function(e){
                $("#categories_input-2").val(JSON.stringify($('#category_order-2.dd').nestable('serialize')));
            });
            $("#stream_categories_form-3").submit(function(e){
                $("#categories_input-3").val(JSON.stringify($('#category_order-3.dd').nestable('serialize')));
            });
            $("#stream_categories_form-4").submit(function(e){
                $("#categories_input-4").val(JSON.stringify($('#category_order-4.dd').nestable('serialize')));
            });
        });
        </script>
    </body>
</html>