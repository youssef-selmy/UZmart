<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Rest;

set_time_limit(0);
ini_set('memory_limit', '4G');

use App\Http\Controllers\Controller;
use App\Models\ActiveReferral;
use App\Models\AssignShopTag;
use App\Models\BackupHistory;
use App\Models\Banner;
use App\Models\BannerProduct;
use App\Models\BannerTranslation;
use App\Models\Blog;
use App\Models\BlogTranslation;
use App\Models\Bonus;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\Coupon;
use App\Models\CouponTranslation;
use App\Models\Currency;
use App\Models\DeliveryManSetting;
use App\Models\Discount;
use App\Models\EmailSetting;
use App\Models\EmailSubscription;
use App\Models\EmailTemplate;
use App\Models\ExtraGroup;
use App\Models\ExtraGroupTranslation;
use App\Models\ExtraValue;
use App\Models\Faq;
use App\Models\FaqTranslation;
use App\Models\Gallery;
use App\Models\Invitation;
use App\Models\Language;
use App\Models\Like;
use App\Models\MetaTag;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\Order;
use App\Models\OrderCoupon;
use App\Models\OrderDetail;
use App\Models\OrderRefund;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\PaymentPayload;
use App\Models\PaymentProcess;
use App\Models\Payout;
use App\Models\Point;
use App\Models\PointHistory;
use App\Models\PrivacyPolicy;
use App\Models\PrivacyPolicyTranslation;
use App\Models\Product;
use App\Models\ProductProperty;
use App\Models\ProductTranslation;
use App\Models\Referral;
use App\Models\ReferralTranslation;
use App\Models\Review;
use App\Models\Settings;
use App\Models\Shop;
use App\Models\ShopClosedDate;
use App\Models\ShopDeliverymanSetting;
use App\Models\ShopGallery;
use App\Models\ShopPayment;
use App\Models\ShopSubscription;
use App\Models\ShopTag;
use App\Models\ShopTagTranslation;
use App\Models\ShopTranslation;
use App\Models\ShopWorkingDay;
use App\Models\SmsGateway;
use App\Models\SmsPayload;
use App\Models\SocialProvider;
use App\Models\Story;
use App\Models\Subscription;
use App\Models\Tag;
use App\Models\TagTranslation;
use App\Models\TermCondition;
use App\Models\TermConditionTranslation;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Translation;
use App\Models\Unit;
use App\Models\UnitTranslation;
use App\Models\User;
use App\Models\UserCart;
use App\Models\UserPoint;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Traits\ApiResponse;
use Firebase\JWT\JWT;
use Http;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    use ApiResponse;

    public function bosYaTest()
    {
        $id   = '65b2631e2b4e1d94b97f2d20';
        $time = time();
        $data = [
            'id'     => $id,
            'msisdn' => '9647835077893',
            'iat'    => $time,
            'exp'    => $time + 60 * 60 * 4
        ];

        //https://test.zaincash.iq/transaction/pay?id=65b0eee72b4e1d94b97f2ca7
        $newToken = JWT::encode($data, $payload['key'] ?? '$2y$10$hBbAZo2GfSSvyqAyV2SaqOfYewgYpfR1O19gIh4SqyGWdmySZYPuS' ,'HS256');

        $rUrl = 'https://test.zaincash.iq/transaction/get';

//        if (false) {
//            $rUrl = 'https://api.zaincash.iq/transaction/get';
//        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])
            ->post($rUrl, [
                'token'      => $newToken,
                'merchantId' => '5ffacf6612b5777c6d44266f',
                'lang'       => $this->language ?? 'iq'
            ]);

        $encode = json_encode([
            'request'  => [
                'url' => ($payload['url'] ?? 'https://test.zaincash.iq') . '/transaction/get',
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => [
                    'token'      => $newToken,
                    'merchantId' => $payload['merchantId'] ?? '5ffacf6612b5777c6d44266f',
                    'lang'       => $this->language ?? 'iq'
                ]
            ],
            'response' => [
                'data'   => $response->json(),
                'status' => $response->status(),
            ]
        ]);

        dd($encode, json_decode($encode));

        dd("mb:" . memory_get_usage() / 1024 / 1024, "mb:" . memory_get_usage(true) / 1024 / 1024);
//        $translations = [
//            "dashboard" => "Dashboard",
//            "shops" => "Shops",
//            "catalog" => "Catalog",
//            "orders" => "Orders",
//            "pos.system" => "POS System",
//            "banners" => "Banners",
//            "tickets" => "Tickets",
//            "notifications" => "Notifications",
//            "subscriptions" => "Subscriptions",
//            "delivery" => "Delivery",
//            "gallery" => "Gallery",
//            "blogs" => "Blogs",
//            "galleries" => "Galleries",
//            "settings" => "Settings",
//            "products" => "Products",
//            "extras" => "Extras",
//            "categories" => "Categories",
//            "brands" => "Brands",
//            "units" => "Units",
//            "settings.general" => "General settings",
//            "languages" => "Languages",
//            "currencies" => "Currencies",
//            "translations" => "Translations",
//            "backup" => "Backup",
//            "system.information" => "System information",
//            "payments" => "Payments",
//            "clients" => "Clients",
//            "roles" => "Roles",
//            "stores" => "Stores",
//            "invites" => "Invites",
//            "faqs" => "FAQs",
//            "transactions" => "Transactions",
//            "all" => "All",
//            "paid" => "Paid",
//            "sms-gateways" => "Sms gateways",
//            "terms" => "Terms & Conditions",
//            "policy" => "Privacy & Policy",
//            "reviews" => "Reviews",
//            "product.review" => "Product review",
//            "order.review" => "Order review",
//            "product.reviews" => "Product reviews",
//            "order.reviews" => "Order reviews",
//            "deliveryboy.reviews" => "Deliveryman reviews",
//            "order" => "Order",
//            "rating" => "Rating",
//            "order.id" => "Order ID",
//            "comment" => "Comment",
//            "product.id" => "Product ID",
//            "update" => "Update",
//            "update.database" => "Update database",
//            "coupons" => "Coupons",
//            "shop.users" => "Shop users",
//            "order.status" => "Order status",
//            "equal" => "Equal",
//            "stories" => "Stories",
//            "cashback" => "Cashback",
//            "order.management" => "Order management",
//            "report.management" => "Report management",
//            "food.management" => "Food management",
//            "customer.management" => "Customer management",
//            "deliveryman.management" => "Deliveryman management",
//            "content.management" => "Content management",
//            "transaction.management" => "Transaction management",
//            "food.report" => "Food report",
//            "order.report" => "Order report",
//            "category.report" => "Category report",
//            "system.settings" => "System settings",
//            "business.settings" => "Business settings",
//            "notification.settings" => "Notification settings",
//            "page.setup" => "Page setup",
//            "referral" => "Referral",
//            "food" => "Food",
//            "add.food" => "Add food",
//            "delete.food" => "Are you sure to delete this food?",
//            "set.active.food" => "Are you sure to set active this food?",
//            "edit.food" => "Edit food",
//            "food.extras" => "Food extras",
//            "categorys" => "Categorys",
//            "category" => "Category",
//            "brand" => "Brand",
//            "product" => "Product",
//            "extra" => "Extra",
//            "cancel" => "Cancel",
//            "save" => "Save",
//            "add" => "Add",
//            "id" => "ID",
//            "title" => "Title",
//            "description" => "Description",
//            "type" => "Type",
//            "extra.groups" => "Extra groups",
//            "color" => "Color",
//            "text" => "Text",
//            "image" => "Image",
//            "value" => "Value",
//            "extra.group" => "Extra group",
//            "accept" => "Accept",
//            "reject" => "Reject",
//            "question" => "Question",
//            "answer" => "Answer",
//            "language" => "Language",
//            "en" => "English",
//            "de" => "Deutsch",
//            "ru" => "Русский",
//            "month" => "Month",
//            "minute" => "Minute",
//            "day" => "Day",
//            "result" => "Result",
//            "filter" => "Filter",
//            "select" => "Select",
//            "project.settings" => "Project settings",
//            "commission" => "Commission",
//            "favicon" => "Favicon",
//            "map" => "Map",
//            "config" => "Config",
//            "google.map.key" => "Google map key",
//            "google.firebase.key" => "Google firebase key",
//            "multi.seller" => "Multi seller",
//            "single.seller" => "Single seller",
//            "status" => "Status",
//            "new" => "New",
//            "accepted" => "Accepted",
//            "ready" => "Ready",
//            "on_a_way" => "On a way",
//            "delivered" => "Delivered",
//            "canceled" => "Canceled",
//            "edited" => "Edited",
//            "approved" => "Approved",
//            "rejected" => "Rejected",
//            "active" => "Active",
//            "open" => "Open",
//            "completed" => "Completed",
//            "progress" => "Progress",
//            "standard" => "Standard",
//            "express" => "Express",
//            "free" => "Free",
//            "point" => "Point",
//            "processed" => "Processed",
//            "admin" => "Admin",
//            "seller" => "Seller",
//            "manager" => "Manager",
//            "moderator" => "Moderator",
//            "deliveryman" => "Deliveryman",
//            "deliverymans" => "Deliverymans",
//            "users" => "Users",
//            "user.id" => "User ID",
//            "client" => "Client",
//            "client.add" => "Add new client",
//            "firstname" => "First name",
//            "lastname" => "Last name",
//            "email" => "Email",
//            "role" => "Role",
//            "options" => "Options",
//            "gender" => "Gender",
//            "user" => "User",
//            "birthday" => "Birthday",
//            "phone" => "Phone",
//            "address" => "Address",
//            "currency" => "Currency",
//            "client.title" => "Client title",
//            "secret.title" => "Secret title",
//            "client.id" => "Client ID",
//            "secret.id" => "Secret ID",
//            "fullname" => "Full name",
//            "wallet" => "Wallet",
//            "topup.wallet" => "Topup wallet",
//            "user.settings" => "User settings",
//            "created.by" => "Created by",
//            "tax" => "Tax",
//            "open_close.time" => "Open & close time",
//            "logo" => "Logo",
//            "background" => "Background",
//            "payment.type" => "Payment type",
//            "cash" => "Cash",
//            "terminal" => "Terminal",
//            "amount" => "Amount",
//            "created.at" => "Created at",
//            "number.of.products" => "Number of products",
//            "customer.details" => "Customer details",
//            "clear.all" => "Clear all",
//            "sub.total" => "Sub total",
//            "total.amount" => "Total amount",
//            "place.order" => "Place order",
//            "bag" => "Bag",
//            "shipping.info" => "Shipping information",
//            "payment.status" => "Payment status",
//            "coupon" => "Coupon",
//            "check.coupon" => "Check coupon",
//            "new.order" => "New order",
//            "create" => "Create",
//            "order.details" => "Order details",
//            "order.tax" => "Order tax",
//            "product.tax" => "Product tax",
//            "view.order" => "View order",
//            "cancelled.orders" => "Cancelled orders",
//            "delivered.orders" => "Delivered orders",
//            "total.products" => "Total products",
//            "total.earned" => "Total earned",
//            "delivery.earning" => "Delivery earning",
//            "total.order.tax" => "Total order tax",
//            "total.comission" => "Total comission",
//            "last.30.days" => "Last 30 days",
//            "this.month" => "This month",
//            "this.week" => "This week",
//            "this.year" => "This year",
//            "total.orders.count" => "Total orders count",
//            "top.customers" => "Top customers",
//            "top.selled.products" => "Top selled products",
//            "sales" => "Sales",
//            "total.sales.amount" => "Total sales amount",
//            "delivery.range" => "Delivery range",
//            "delivery.date" => "Delivery date",
//            "delivery.time" => "Delivery time",
//            "delivery.type" => "Delivery type",
//            "delivery.address" => "Delivery address",
//            "delivery.fee" => "Delivery fee",
//            "transaction" => "Transaction",
//            "transaction.id" => "Transaction ID",
//            "price" => "Price",
//            "status.description" => "Status description",
//            "note" => "Note",
//            "up.to" => "up to",
//            "clear" => "Clear",
//            "delete.selected" => "Delete selected",
//            "change.user.role" => "Change user role",
//            "change.language" => "Change language",
//            "order.amount" => "Order amount",
//            "add.extra.group" => "Add extra group",
//            "add.extra" => "Add extra",
//            "add.order" => "Add order",
//            "add.faq" => "Add FAQ",
//            "add.delivery" => "Add delivery",
//            "add.deliveryman" => "Add deliveryman",
//            "add.product" => "Add product",
//            "add.category" => "Add category",
//            "add.brand" => "Add brand",
//            "add.unit" => "Add unit",
//            "add.banner" => "Add banner",
//            "add.ticket" => "Add ticket",
//            "add.notification" => "Add notification",
//            "add.client" => "Add client",
//            "add.address" => "Add address",
//            "add.blog" => "Add blog",
//            "add.language" => "Add language",
//            "add.currency" => "Add currency",
//            "add.translation" => "Add translation",
//            "add.coupon" => "Add coupon",
//            "add.discount" => "Add discount",
//            "edit" => "Edit",
//            "edit.extra.group" => "Edit extra group",
//            "edit.extra" => "Edit extra",
//            "edit.payment" => "Edit payment",
//            "edit.faq" => "Edit FAQ",
//            "edit.user" => "Edit user",
//            "edit.order" => "Edit order",
//            "edit.delivery" => "Edit delivery",
//            "edit.deliveryman" => "Edit deliveryman",
//            "edit.product" => "Edit product",
//            "edit.category" => "Edit category",
//            "edit.brand" => "Edit brand",
//            "edit.unit" => "Edit unit",
//            "edit.banner" => "Edit banner",
//            "edit.ticket" => "Edit ticket",
//            "edit.notification" => "Edit notification",
//            "edit.client" => "Edit client",
//            "edit.blog" => "Edit blog",
//            "edit.language" => "Edit language",
//            "edit.currency" => "Edit currency",
//            "edit.translation" => "Edit translation",
//            "edit.coupon" => "Edit coupon",
//            "edit.discount" => "Edit discount",
//            "please.select.client" => "Please, select client",
//            "please.select.client.address" => "Please, select client address",
//            "please.select.payment.type" => "Please, select payment type",
//            "select.extra.type" => "Select extra type",
//            "select.extra.group" => "Select extra group",
//            "select.payment.type" => "Select payment type",
//            "select.address" => "Select address",
//            "select.currency" => "Select currency",
//            "select.client" => "Select client",
//            "select.category" => "Select category",
//            "select.brand" => "Select brand",
//            "select.group" => "Select group",
//            "select.role" => "Select role",
//            "deliveryman.order.acceptance.time" => "Deliveryman order acceptance time",
//            "do.you.really.want.to.clear.the.cash?" => "Do you really want to clear the cache?",
//            "clear.cash" => "Clear cache",
//            "backup.loading" => "Please wait a little while it takes time",
//            "delivery.boy" => "Delivery boy",
//            "customer.info" => "Customer info",
//            "Welcome.to.Dashboard!" => "Welcome to Dashboard!",
//            "deliveries.map" => "Deliveries map",
//            "show.locations" => "Show locations on Map",
//            "not.equal" => "Not equal",
//            "report" => "Report",
//            "download" => "Download",
//            "delivery.zone" => "Delivery zone",
//            "in.progress.orders" => "In progress orders",
//            "refund.details" => "Refund details",
//            "refunds" => "Refunds",
//            "welcome.manager" => "welcome manager",
//            "get.started" => "Get started",
//            "order.created.successfully" => "Order was successfully created",
//            "please.select.delivery" => "Place select delivery",
//            "please.select.address" => "Please, select address",
//            "please.select.currency" => "Place select currency",
//            "edit.bonus" => "Edit bonus",
//            "edit.story" => "Edit story",
//            "edit.profile" => "Edit profile",
//            "add.brands" => "Add brands",
//            "min.price" => "Min price of delivery",
//            "price.per.km" => "Price per km",
//            "add.payment" => "Add payment",
//            "payment" => "Payment",
//            "set.active.delivery" => "Are you sure to set active this delivery?",
//            "email.setting.id" => "Email settings id",
//            "email.provider" => "Email provider",
//            "unpublished" => "Unpublished",
//            "pending" => "Pending",
//            "published" => "Published",
//            "page.not.found" => "Page not found",
//            "go.back" => "Go back",
//            "send.to" => "Send to",
//            "body" => "Body",
//            "alt.body" => "Alt body",
//            "verify" => "Verify",
//            "subscribe" => "Subscribe",
//            "email.settings" => "Email settings",
//            "add.cashback" => "Add cashback",
//            "delete" => "Are you sure to delete ?",
//            "all.delete" => "Are you sure you want to delete all products?",
//            "select.the.product" => "Select the product you want to delete",
//            "extra.group.value" => "Extra group value",
//            "extra.value" => "Extra value",
//            "not.subscriber" => "Not subscriber",
//            "add.subciribed" => "Add subciribed",
//            "filter.result" => "Filter result",
//            "add.subscriber" => "Add subscriber",
//            "message.subscriber" => "Message subscriber",
//            "subscriber" => "Subscriber",
//            "delete.all" => "Delete all",
//            "email.subscriber" => "Email subscriber",
//            "bonus.stock.quantity" => "Bonus product quantity",
//            "stock.bonus" => "Product bonus",
//            "add.bonus" => "Add bonus",
//            "product.quantity" => "Product quantity",
//            "bonus.stock" => "Bonus stock",
//            "bonus" => "Bonus",
//            "enter.extra.value" => "Enter extra value",
//            "yes" => "Yes",
//            "no" => "No",
//            "logo.image" => "Logo image",
//            "background.image" => "Background image",
//            "status.note" => "Status note",
//            "visibility" => "Visibility",
//            "general" => "General",
//            "open.hours" => "Open hours",
//            "close.hours" => "Close hours",
//            "order.info" => "Order info",
//            "min.amount" => "Min amount",
//            "admin.comission" => "Admin comission",
//            "payouts" => "Payouts",
//            "withdraw" => "Withdraw",
//            "withdraw.request" => "Withdraw request",
//            "submit" => "Submit",
//            "payout.status" => "Payout status",
//            "payout.requests" => "Payout requests",
//            "pay.to.seller" => "Pay to seller",
//            "requested.amount" => "Requested amount",
//            "pay" => "Pay",
//            "wallets" => "Wallets",
//            "upload" => "Upload",
//            "name" => "Name",
//            "export" => "Export",
//            "product.extras" => "Product extras",
//            "product.property" => "Product property",
//            "food.property" => "Food property",
//            "finish" => "Finish",
//            "unit" => "Unit",
//            "min.qty" => "Min qty",
//            "max.qty" => "Max qty",
//            "images" => "Images",
//            "prev" => "Prev",
//            "quantity" => "Quantity",
//            "key" => "Key",
//            "min.quantity" => "Min quantity",
//            "max.quantity" => "Max quantity",
//            "product.info" => "Product info",
//            "child.categories" => "Child categories",
//            "keywords" => "Keywords",
//            "parent.category" => "Parent category",
//            "position" => "Position",
//            "delivery.date.&.time" => "Delivery date & time",
//            "total" => "Total",
//            "product.total" => "Product total",
//            "order.total" => "Order total",
//            "url" => "Url",
//            "ask.for.question" => "Ask for question",
//            "subject" => "Subject",
//            "content" => "Content",
//            "update.status" => "Update status",
//            "ticket" => "Ticket",
//            "answered" => "Answered",
//            "closed" => "Closed",
//            "published.at" => "Published at",
//            "publish" => "Publish",
//            "avatar" => "Avatar",
//            "password.confirmation" => "Password confirmation",
//            "short.description" => "Short description",
//            "symbol" => "Symbol",
//            "rate" => "Rate",
//            "group" => "Group",
//            "web" => "Web",
//            "mobile" => "Mobile",
//            "errors" => "Errors",
//            "do.you.care.about.your.data" => "Do you care about your data?",
//            "here.you.can.take.backup.from.database" => "Here you can take backup from database.",
//            "download.backup" => "Download backup",
//            "sms.gateway" => "Sms gateway",
//            "from" => "From",
//            "firebase.config" => "Firebase config",
//            "api.key" => "API key",
//            "server.key" => "Server key",
//            "vapid.key" => "VAPID key",
//            "auth.domain" => "Auth domain",
//            "project.id" => "Project ID",
//            "storage.bucket" => "Storage bucket",
//            "messaging.sender.id" => "Messaging sender ID",
//            "app.id" => "App ID",
//            "measurement.id" => "Measurement ID",
//            "logout" => "Logout",
//            "leave.site" => "Do you really want to leave the site?",
//            "translation" => "Translation",
//            "you.cannot.order.more.than" => "You cannot order more than",
//            "selected" => "Selected",
//            "successfully.closed" => "Successfully closed",
//            "required" => "Required",
//            "required.field" => "Required field",
//            "back" => "Back",
//            "print" => "Print",
//            "invoice" => "Invoice",
//            "discount" => "Discount",
//            "sub-total.amount" => "Sub - total amount",
//            "delivery.price" => "Delivery price",
//            "grand.total" => "Grand total",
//            "copied.to.clipboard" => "Copied to clipboard",
//            "open.close.time" => "Open & close time",
//            "invitation.link" => "Invitation link",
//            "copy.invitation.link" => "Copy invitation link",
//            "product.name" => "Product name",
//            "total.price" => "Total price",
//            "expired.at" => "Expired at",
//            "fix" => "Fix",
//            "percent" => "Percent",
//            "start.date" => "Start date",
//            "end.date" => "End date",
//            "accept.invite" => "Accept invite",
//            "from.day" => "From day",
//            "to.day" => "To day",
//            "password.do.not.match" => "The passwords do not match!",
//            "male" => "Male",
//            "female" => "Female",
//            "password" => "Password",
//            "footer.text" => "Footer text",
//            "social.settings" => "Social settings",
//            "cannot.work.demo" => "You cannot use it in demo mode!",
//            "purchase.subscription" => "Purchase subscription",
//            "purchase" => "Purchase",
//            "will.expire.at" => "will expire at",
//            "subscription" => "Subscription",
//            "insufficient.balance" => "Insufficient balance",
//            "footer" => "Footer",
//            "hours.ago" => "hours ago",
//            "minutes.ago" => "minutes ago",
//            "last.activity" => "Last activity",
//            "active.orders" => "Active orders",
//            "distance" => "Distance",
//            "driver.information" => "Driver information",
//            "vehicle" => "Vehicle",
//            "fuel" => "Fuel",
//            "customer.information" => "Customer information",
//            "analitics.and.reports" => "Analitics and reports",
//            "support.team" => "support team",
//            "app.settings" => "App Settings",
//            "edit.email.provider" => "Edit email provider",
//            "add.sms.geteway" => "Add sms geteway",
//            "not.published" => "Not published",
//            "successfully.created" => "Successfully created",
//            "successfully.updated" => "Successfully updated",
//            "successfully.published" => "Successfully published",
//            "successfully.deleted" => "Successfully deleted",
//            "successfully.purchased" => "Successfully purchased",
//            "successfully.added" => "Successfully added",
//            "publish.notification" => "Publish notification",
//            "publish.blog" => "Publish blog",
//            "change.default.language" => "Change default language",
//            "choose.discount.date" => "Choose discount date",
//            "reject.invite" => "Are you sure to reject this invite?",
//            "set.active.product" => "Are you sure to set active this product?",
//            "set.active.unit" => "Are you sure to set active this unit?",
//            "set.active.banner" => "Are you sure to set active this banner?",
//            "set.active.notification" => "Are you sure to set active this notification?",
//            "set.active.blog" => "Are you sure to set active this blog?",
//            "set.active.payment" => "Are you sure to set active this payment?",
//            "set.active.sms.gateway" => "Are you sure to set active this sms gateway?",
//            "set.active.faq" => "Are you sure to set active this FAQ?",
//            "set.active.discount" => "Are you sure to set active this discount?",
//            "delete.product" => "Are you sure to delete this product?",
//            "delete.extra.group" => "Are you sure to delete this extra group?",
//            "delete.extra" => "Are you sure to delete this extra?",
//            "delete.category" => "Are you sure to delete this category?",
//            "delete.brand" => "Are you sure to delete this brand?",
//            "delete.banner" => "Are you sure to delete this banner?",
//            "delete.review" => "Are you sure to delete this review?",
//            "delete.notification" => "Are you sure to delete this notification?",
//            "delete.blog" => "Are you sure to delete this blog?",
//            "delete.language" => "Are you sure to delete this language?",
//            "delete.currency" => "Are you sure to delete this currency?",
//            "delete.faq" => "Are you sure to delete this FAQ?",
//            "delete.coupon" => "Are you sure to delete this coupon?",
//            "delete.discount" => "Are you sure to delete this discount?",
//            "from.order" => "from",
//            "spent.since.registration" => "Spent since registration",
//            "change.status" => "Change status",
//            "documents" => "Documents",
//            "date" => "Date",
//            "document" => "Document",
//            "delivery.reciept" => "Delivery reciept",
//            "registration.date" => "Registration date",
//            "messages" => "Messages",
//            "created.date.&.time" => "Created date & time",
//            "overview" => "Overview",
//            "revenue" => "Revenue",
//            "variation" => "Variation",
//            "user.info" => "User information",
//            "ordered.products" => "Ordered products",
//            "successfull.orders" => "Successfull orders",
//            "order.refunds" => "Order refunds",
//            "create.payout" => "Create payout",
//            "select.seller" => "Select seller",
//            "hello" => "Hello",
//            "hello.text" => "Let's check your stats today!",
//            "top.products" => "Top products",
//            "all.orders" => "All orders",
//            "point.orders" => "Point orders",
//            "change.columns" => "Change columns",
//            "archive" => "Archive",
//            "scheduled.orders" => "Scheduled orders",
//            "payment.payloads" => "Payment payloads",
//            "edit.payment.payloads" => "Edit payment payload",
//            "add.payment.payloads" => "Add payment payload",
//            "payment.id" => "Payment id",
//            "paypal.mode" => "Paypal mode",
//            "paypal.sandbox.client.id" => "Paypal sandbox client id",
//            "paypal.sandbox.client.secret" => "Paypal sandbox client secret",
//            "paypal.sandbox.app.id" => "Paypal sandbox app id",
//            "paypal.live.client.id" => "Paypal live client id",
//            "paypal.live.client.secret" => "Paypal live client secret",
//            "paypal.live.app.id" => "Paypal live app id",
//            "paypal.payment.action" => "Paypal payment action",
//            "paypal.currency" => "Paypal currency",
//            "paypal.locale" => "Paypal locale",
//            "paypal.validate.ssl" => "Paypal validate ssl",
//            "stripe.pk" => "Stripe public key",
//            "stripe.sk" => "Stripe secret key",
//            "razorpay.key" => "Razorpay key",
//            "razorpay.secret" => "Razorpay secret",
//            "boxes" => "Boxes",
//            "edit.box" => "Edit box",
//            "discount.type" => "Discount type",
//            "discount.price" => "Discount price",
//            "boxes.categories" => "Boxes categories",
//            "add.box" => "Add box",
//            "total.time" => "Total time",
//            "calories" => "Calories",
//            "servings" => "Servings",
//            "add.nutrition" => "Add nutrition",
//            "instructions" => "Instructions",
//            "weight" => "Weight",
//            "percentage" => "Percentage",
//            "box" => "Box",
//            "box.category" => "Box category",
//            "nutritions" => "Nutritions",
//            "paystack.sk" => "Paystack secret key",
//            "paystack.pk" => "Paystack public key"
//        ];
//
//        foreach ($translations as $key => $translation) {
//            Translation::updateOrCreate([
//                'key'    => $key,
//            ], [
//                'locale' => 'en',
//                'group'  => 'web',
//                'value'  => $translation,
//                'status' => 1,
//            ]);
//        }
//        $model = (new UserActivityService);
//        $model->create(1, 'bosya', 'bosya', 1, User::first());
    }

    public function gigLogistic(): array
    {
        $headers = [
            'Content-Type' => 'application/json'
        ];

        $response = Http::withHeaders($headers)->post(
            'https://giglthirdpartyapitestenv.azurewebsites.net/api/thirdparty/login',
            [
                'username' => '',
                'Password' => '',
//                'SessionObj' => '',
            ]
        );

        $test = Http::withHeaders($headers)->post(
            'http://test.giglogisticsse.com/api/thirdparty/login',
            [
                'username' => '',
                'Password' => '',
//                'SessionObj' => '',
            ]
        );

        return [
            'production' => [
                'url'    => 'https://giglthirdpartyapitestenv.azurewebsites.net/api/thirdparty/login',
                'e_uri'  => $response->effectiveUri(),
                'status' => $response->status(),
                'body'   => json_decode($response->body()),
            ],
            'test' => [
                'url'    => 'http://test.giglogisticsse.com/api/thirdparty/login',
                'e_uri'  => $response->effectiveUri(),
                'status' => $test->status(),
                'body'   => json_decode($test->body()),
            ]
        ];
    }

    public function allModels(): bool|string
    {
        $tables = collect([
            (new ActiveReferral)->getTable(),
            (new AssignShopTag)->getTable(),
            (new BackupHistory)->getTable(),
            (new Banner)->getTable(),
            (new BannerProduct)->getTable(),
            (new BannerTranslation)->getTable(),
            (new Blog)->getTable(),
            (new BlogTranslation)->getTable(),
            (new Bonus)->getTable(),
            (new Brand)->getTable(),
            (new Cart)->getTable(),
            (new CartDetail)->getTable(),
            (new Category)->getTable(),
            (new CategoryTranslation)->getTable(),
            (new Coupon)->getTable(),
            (new CouponTranslation)->getTable(),
            (new Currency)->getTable(),
            (new DeliveryManSetting)->getTable(),
            (new Discount)->getTable(),
            (new EmailSetting)->getTable(),
            (new EmailSubscription)->getTable(),
            (new EmailTemplate)->getTable(),
            (new ExtraGroup)->getTable(),
            (new ExtraGroupTranslation)->getTable(),
            (new ExtraValue)->getTable(),
            (new Faq)->getTable(),
            (new FaqTranslation)->getTable(),
            (new Gallery)->getTable(),
            (new Invitation)->getTable(),
            (new Language)->getTable(),
            (new Like)->getTable(),
            (new MetaTag)->getTable(),
            (new Notification)->getTable(),
            (new NotificationUser)->getTable(),
            (new Order)->getTable(),
            (new OrderCoupon)->getTable(),
            (new OrderDetail)->getTable(),
            (new OrderRefund)->getTable(),
            (new OrderStatus)->getTable(),
            (new Payment)->getTable(),
            (new PaymentPayload)->getTable(),
            (new PaymentProcess)->getTable(),
            (new Payout)->getTable(),
            (new Point)->getTable(),
            (new PointHistory)->getTable(),
            (new PrivacyPolicy)->getTable(),
            (new PrivacyPolicyTranslation)->getTable(),
            (new Product)->getTable(),
            (new ProductProperty)->getTable(),
            (new ProductTranslation)->getTable(),
            (new Referral)->getTable(),
            (new ReferralTranslation)->getTable(),
            (new Review)->getTable(),
            (new Settings)->getTable(),
            (new Shop)->getTable(),
            (new ShopClosedDate)->getTable(),
            (new ShopDeliverymanSetting)->getTable(),
            (new ShopGallery)->getTable(),
            (new ShopPayment)->getTable(),
            (new ShopSubscription)->getTable(),
            (new ShopTag)->getTable(),
            (new ShopTagTranslation)->getTable(),
            (new ShopTranslation)->getTable(),
            (new ShopWorkingDay)->getTable(),
            (new SmsGateway)->getTable(),
            (new SmsPayload)->getTable(),
            (new SocialProvider)->getTable(),
            (new Story)->getTable(),
            (new Subscription)->getTable(),
            (new Tag)->getTable(),
            (new TagTranslation)->getTable(),
            (new TermCondition)->getTable(),
            (new TermConditionTranslation)->getTable(),
            (new Ticket)->getTable(),
            (new Transaction)->getTable(),
            (new Translation)->getTable(),
            (new Unit)->getTable(),
            (new UnitTranslation)->getTable(),
            (new User)->getTable(),
            (new UserCart)->getTable(),
            (new UserPoint)->getTable(),
            (new Wallet)->getTable(),
            (new WalletHistory)->getTable(),
        ]);

        $columns = collect();

        foreach ($tables as $table) {

            $list = DB::getSchemaBuilder()->getColumnListing($table);

            if (!empty($list)) {
                $columns->push(...$list);
            }

        }

        return json_encode($columns->unique()->values()->toArray());
    }
}
