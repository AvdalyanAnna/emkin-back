<?php

Route::prefix('conversations')->group(function () {

    Route::controller('ConversationController')->group(function () {
        Route::get('/', 'getConversations');
        Route::get('tickets-conversations', 'getConversationsTickets')->middleware('role:admin|moderator');
        Route::post('create', 'addConversations')->middleware('check_blocked_users');
        Route::delete('/', 'deleteConversations');
        Route::get('download-{media}', 'download');
        Route::get('moderators', 'moderatorList')->middleware('role:admin|moderator');

        Route::prefix('{conversation}')->group(function () {
            Route::put('change-moderator', 'changeParticipant')->middleware('role:admin|moderator');
            Route::post('add-complaint', 'addComplaint');
            Route::put('close', 'closeConversation');
            Route::put('{action}', 'actionReadAllAndClear')->where('action', 'readAll|clear');

            Route::controller('MessageController')->group(function () {
                Route::prefix('messages')->group(function () {
                    Route::post('/', 'store')->middleware('check_conversation_participants');
                    Route::get('/', 'messages');
                    Route::get('{message}/{action}', 'messageActions')->where('action', 'markRead|delete');
                });
            });
        });
    });
});
