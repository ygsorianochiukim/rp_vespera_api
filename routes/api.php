<?php
use App\Domain\UploadReview\Models\UploadReview;
use App\Domain\AutomationDashboard\Models\AutomationDashboard;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AutoForfeitureController;
use App\Http\Controllers\Api\V1\AutomationDashboardController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\ChatHistoryController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\IssuesController;
use App\Http\Controllers\Api\V1\PaymentModuleController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\V1\UploadReviewController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {});
Route::get('issues', [IssuesController::class, 'index']);
Route::post('issues/store', [IssuesController::class, 'store']);
Route::put('issues/updateissues', [IssuesController::class, 'update']);
Route::delete('issues/removeissues', [IssuesController::class, 'destroy']);

Route::get('conversation/logs', [ConversationController::class, 'displayHandsoff']);
Route::put('conversation/update', [ConversationController::class, 'updateTransferLogs']);
Route::put('conversation/updatestatuslogs', [ConversationController::class, 'updateStatusLogs']);
Route::get('conversation/logs', [ConversationController::class , 'displayHandsoff']);
Route::put('conversation/updateConversation', [ConversationController::class , 'updateConversation']);
Route::put('conversation/updateBot', [ConversationController::class , 'updateTransferLogsBot']);
Route::put('conversation/updateLeadsStatus', [ConversationController::class , 'updateLeadsStatus']);
Route::put('conversation/updateLeadsRelationship', [ConversationController::class , 'updateLeadsRelationship']);
Route::get('conversation/list', [ConversationController::class , 'index']);
Route::get('conversation/fetchpsid/{psid}', [ConversationController::class , 'fetchCustomerPSID']);
Route::get('conversation/summary', [ConversationController::class , 'leadsSummary']);
Route::apiResource('conversation', ConversationController::class);

Route::put('automationdashboard/updateConversation', [AutomationDashboardController::class , 'updateConversationLogs']);
Route::get('automationdashboard/summary', [AutomationDashboardController::class , 'summary']);
Route::apiResource('automationdashboard', AutomationDashboardController::class);

Route::get('history', [ChatHistoryController::class , 'index']);
Route::post('history/newLogs', [ChatHistoryController::class , 'newHistoryLogs']);

Route::get('/user', [AuthController::class, 'user']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/logout-all', [AuthController::class, 'logoutAll']);
Route::post('/tokens', [AuthController::class, 'createApiToken']);
Route::get('/tokens', [AuthController::class, 'tokens']);
Route::delete('/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
Route::get('/test', [TestController::class, 'indexTest']);
Route::get('/test1', [BillingController::class, 'index']);
Route::get('/test2', [BillingController::class, 'index1']);
Route::get('/test3', [BillingController::class, 'index2']);

//AUTO FORFEITE FUNCTION SAVE
Route::get('/readsheet', [AutoForfeitureController::class, 'readGoogleSheet']);
Route::get('/forfeiture', [AutoForfeitureController::class, 'getAgedData']);
Route::post('/saveDocTReference', [AutoForfeitureController::class, 'saveToDocTReference']);
Route::post('/getPreownDue', [AutoForfeitureController::class, 'getPreownDue']);
Route::post('/saveToForfeiture', [AutoForfeitureController::class, 'saveToForfeiture']);
Route::post('/saveToForfeitureLine', [AutoForfeitureController::class, 'saveToForfeitureLine']);
Route::post('/saveToForfeitureSignee', [AutoForfeitureController::class, 'saveToForfeitureSignee']);
Route::post('/saveToForfeitureSigneePR', [AutoForfeitureController::class, 'saveToForfeitureSigneePR']);
Route::get('/updateDocTReference/{docTReferenceId}', [AutoForfeitureController::class, 'updateToDocTReference']);

//AUTO FORFEITE FUNCTION UPDATE
Route::post('/updateToForfeiture', [AutoForfeitureController::class, 'updateToForfeiture']);
Route::post('/updateToPreownership', [AutoForfeitureController::class, 'updateToPreownership']);
Route::post('/updateToLot', [AutoForfeitureController::class, 'updateToLot']);

//PAYMENT Module
Route::post('/verifyName', [PaymentModuleController::class, 'verifyBparName']);
Route::post('/verify-otp', [PaymentModuleController::class, 'sendOtp']);
Route::post('/checkOTP', [PaymentModuleController::class, 'verifyOtp']);
Route::get('/checkLots/{bparId}', [PaymentModuleController::class, 'getOwnerLot']);

//UPLOAD REVIEW SAVE
Route::post('/review', [UploadReviewController::class, 'submit']);
//UPLOAD REVIEW FETCH

Route::get('/interments/{occupant}', [UploadReviewController::class, 'getInterments']);

//FETCH REVIEWS
Route::get('/upload-reviews/document/{document_no}',[UploadReviewController::class, 'getByDocumentNo']);


