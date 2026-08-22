/**
 * Chat Alpine.js component for realtime messaging.
 */
function chatApp(conversationId, sendUrl = '/chat/send') {
    return {
        conversationId,
        sendUrl,
        message: '',
        echoChannel: null,
        messagesContainer: null,
        messagesList: null,
        userId: null,
        sending: false,
        messageInput: null,

        initEcho() {
            this.messagesContainer = this.$refs.messagesContainer;
            this.messagesList = this.$refs.messagesList;
            this.messageInput = this.$refs.messageInput;
            this.userId = parseInt(document.querySelector('meta[name="user-id"]')?.content ?? '0', 10);

            // Initial scroll to bottom
            this.$nextTick(() => {
                this.scrollToBottom();
            });

            if (typeof window.Echo !== 'undefined') {
                this.echoChannel = window.Echo.private(`conversation.${this.conversationId}`);

                this.echoChannel.listen('.message.sent', (event) => {
                    this.handleNewMessage(event);
                });
            }
        },

        handleKeydown(event) {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                this.submitMessage();
            }
        },

        // Called directly from message-input form via @submit.prevent or handleKeydown
        async submitMessage() {
            const body = this.message.trim();
            if (this.sending || !body) {
                return;
            }

            this.sending = true;

            // OPTIMISTIC UI: Render immediately, clear input, scroll down
            const tempId = 'temp-' + Date.now();
            const optimisticMessage = {
                id: tempId,
                conversation_id: this.conversationId,
                sender_id: this.userId,
                body: body,
                created_at: new Date().toISOString(),
                sender: { id: this.userId, name: 'You' },
            };

            this.renderAndAppend(optimisticMessage, true);
            this.message = '';
            this.scrollToBottom();

            // Background AJAX request
            try {
                const response = await fetch(this.sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ body }),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                // Replace optimistic message with real one
                if (data.message) {
                    this.replaceOptimisticMessage(tempId, data.message);
                }
            } catch (error) {
                console.error('Failed to send message:', error);
                // On error: remove optimistic message, restore input
                this.removeOptimisticMessage(tempId);
                this.message = body;
            } finally {
                this.sending = false;
            }
        },

        renderAndAppend(message, isOwnMessage = false) {
            const messageHtml = this.renderMessageBubble(message, isOwnMessage);
            this.messagesList.insertAdjacentHTML('beforeend', messageHtml);
        },

        replaceOptimisticMessage(tempId, realMessage) {
            const tempEl = this.messagesList.querySelector(`[data-temp-id="${tempId}"]`);
            if (tempEl) {
                tempEl.outerHTML = this.renderMessageBubble(realMessage, true);
            } else {
                // Fallback: append if not found
                this.renderAndAppend(realMessage, true);
            }
        },

        removeOptimisticMessage(tempId) {
            const tempEl = this.messagesList.querySelector(`[data-temp-id="${tempId}"]`);
            if (tempEl) {
                tempEl.remove();
            }
        },

        handleNewMessage(message) {
            // Skip if it's our own message (already rendered optimistically)
            if (message.sender_id === this.userId) {
                return;
            }

            this.renderAndAppend(message, false);
            this.scrollToBottom();
        },

        renderMessageBubble(message, isOwnMessage = false) {
            const sent = isOwnMessage || message.sender_id === this.userId;
            const time = message.created_at ? new Date(message.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';
            const readAt = message.read_at ? new Date(message.read_at) : null;

            const bubbleClass = sent
                ? 'ml-auto items-end'
                : 'items-start';

            const bubbleStyle = sent
                ? 'rounded-br-md bg-[#E57373] text-white shadow-md shadow-[#E57373]/20'
                : 'rounded-bl-md border border-rose-100 bg-white text-slate-700 shadow-sm';

            const timeClass = sent ? 'text-[#c96767]' : 'text-slate-400';

            let readIndicator = '';
            if (sent) {
                if (readAt) {
                    readIndicator = `
                        <svg class="size-3.5 text-[#c96767]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-label="Read">
                            <polyline points="20 6 9 17 4 12" />
                            <polyline points="14 6 3 17 -2 12" />
                        </svg>
                    `;
                } else {
                    readIndicator = `
                        <svg class="size-3.5 text-[#c96767]/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-label="Delivered">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    `;
                }
            }

            const tempIdAttr = message.id?.toString().startsWith('temp-') ? `data-temp-id="${message.id}"` : '';

            return `
                <article class="flex max-w-[85%] flex-col gap-1.5 sm:max-w-[70%] ${bubbleClass}" ${tempIdAttr}>
                    <div class="rounded-[18px] px-4 py-3 text-sm leading-6 ${bubbleStyle}">
                        ${this.escapeHtml(message.body)}
                    </div>
                    <div class="flex items-center gap-1 px-1 text-xs ${sent ? 'justify-end' : 'justify-start'}">
                        <time class="${timeClass}">${time}</time>
                        ${readIndicator}
                    </div>
                </article>
            `;
        },

        scrollToBottom() {
            if (this.messagesContainer) {
                this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
            }
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
    };
}

window.chatApp = chatApp;
export default chatApp;
