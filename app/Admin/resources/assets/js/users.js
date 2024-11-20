jQuery(document).ready(function ($) {
    /**
     * Client notes
     */
    let showNoteFormBtn = $('[data-client-notes-form-show]');
    let hideNoteFormBtn = $('[data-client-notes-form-hide]');
    let newNoteFormWrapper = $('[data-client-notes-new-form-wrapper]');
    let newNoteTextarea = newNoteFormWrapper.find('textarea');
    let sendNoteBtn = $('[data-client-notes-send-note]');
    let noteList = $('[data-client-notes-list]');
    let noteListItemTemplate = $('[data-client-notes-list-item-template]');

    //client notes
    showNoteFormBtn.click(function (e) {
        e.preventDefault();
        newNoteFormWrapper.slideDown('fast');
    });

    hideNoteFormBtn.click(function (e) {
        e.preventDefault();
        newNoteFormWrapper.slideUp('fast');
        newNoteTextarea.val('');
    });

    $(document).on('click', '[data-client-notes-list-item-edit]', function (e) {
        e.preventDefault();

        let noteId = $(this).attr('data-note-id');
        //prevent new textarea population if exists already
        if ($(`.client-note-list-item[data-note-id=${noteId}] .note-edite-textarea`).length) {
            return;
        }

        $(`[data-client-notes-list-item-edit-btns][data-note-id=${noteId}]`).toggleClass('hidden');
        let noteText = $(`.client-note-list-item[data-note-id=${noteId}] p`);
        let noteTextTextarea = document.createElement('textarea');
        noteTextTextarea.innerHTML = noteText.html();
        noteTextTextarea.rows = 4;
        noteTextTextarea.classList.add('note-edite-textarea');
        noteText.after(noteTextTextarea);
        noteTextTextarea.focus();
        noteText.hide();
    });

    $(document).on('click', '[data-client-notes-list-item-edit-btns] [data-client-notes-cancel-edit]', function (e) {
        e.preventDefault();
        let noteId = $(this).parent().attr('data-note-id');
        $(`[data-client-notes-list-item-edit-btns][data-note-id=${noteId}]`).toggleClass('hidden');
        let noteText = $(
            `.client-note-list-item[data-note-id=${noteId}] p`);
        let noteTextTextarea = $(
            `.client-note-list-item[data-note-id=${noteId}] .note-edite-textarea`);
        noteText.show();
        noteTextTextarea.remove();
    });

    $(document).on('click', '[data-client-notes-list-item-edit-btns] [data-client-notes-edit-note]', function (e) {
        e.preventDefault();
        let noteId = $(this).parent().attr('data-note-id');
        let noteText = $(
            `.client-note-list-item[data-note-id=${noteId}] p`);
        let noteTextTextarea = $(
            `.client-note-list-item[data-note-id=${noteId}] .note-edite-textarea`);
        if (!noteTextTextarea.val()) return;

        Swal.fire({
            title: 'Update note?',
            text: '',
            icon: 'warning',
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then(function (result) {
            if (!result.value) {
                return;
            }
            let clientId = Number.parseInt(newNoteFormWrapper.attr(
                'data-client-notes-new-form-client-id'));
            let action = newNoteFormWrapper.attr(
                'data-client-notes-new-form-action');
            $.ajax({
                type: 'PUT',
                url: action + '/' + noteId,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    noteText: noteTextTextarea.val(),
                },
                success: function (data) {
                    if (data.success === true) {

                        $(`[data-client-notes-list-item-edit-btns][data-note-id=${noteId}]`).toggleClass('hidden');
                        noteText.html(noteTextTextarea.val());
                        noteText.show();
                        noteTextTextarea.remove();

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: data.message,
                        });
                    }
                },
                error(error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: error.responseJSON.message,
                    });
                },
            });
        });
    });

    sendNoteBtn.click(function (e) {
        e.preventDefault();
        let noteText = newNoteTextarea.val();
        if (!noteText) return;

        Swal.fire({
            title: 'Create new note?',
            text: '',
            icon: 'warning',
            showCancelButton: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then(function (result) {
            if (!result.value) {
                return;
            }
            let clientId = Number.parseInt(
                newNoteFormWrapper.attr('data-client-notes-new-form-client-id'));
            let action = newNoteFormWrapper.attr(
                'data-client-notes-new-form-action');
            $.ajax({
                type: 'POST',
                url: action,
                dataType: 'json',
                data: {
                    _token: $('meta[name=csrf-token]').attr('content'),
                    clientId,
                    noteText,
                },
                success: function (data) {
                    if (data.success === true) {
                        noteList.prepend(
                            noteListItemTemplate.html().replace('__note_text__', data.note.text).replace('__created_at__', data.note.created_at).replace('__author_name__', data.note.author.name).replaceAll('__note_id__', data.note.id),
                        );

                        newNoteFormWrapper.hide();
                        newNoteTextarea.val('');

                    } else {
                        Swal.hideLoading();
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: data.message,
                        });
                    }
                    noteList.find('.no-notes-item').hide();
                },
            });
        });

    });

    /**
     * END Client notes
     */

    /**
     * Randomize recipe
     * */
    $(document).on('click change', 'input[type="radio"].random_recipe_distribution_type', function (el) {
        $(document).find('.randomization_type_input').attr('disabled', 'disabled');
        $(document).find('.randomization_type_' + $(this).val()).removeAttr('disabled');
    });

    /**
     * Delete Selected recipes
     * */
    $(document).on('change', '.js-delete-recipes', function (el) {
        let checked = $('.js-delete-recipes:checked').length;
        if (checked > 0) {
            $('#delete-all-selected-recipes').show();
        } else {
            $('#delete-all-selected-recipes').hide();
        }
    });

    $(document).on('change', '.js-delete-recipes', function (el) {
        let alreadySelected = JSON.parse(localStorage.getItem('selected_recipes'));
        if (el.target.checked) {
            alreadySelected.selected.push(el.target.dataset.id);
            localStorage.setItem('selected_recipes', JSON.stringify(alreadySelected));
            return;
        }
        let index = alreadySelected.selected.indexOf(el.target.dataset.id);
        if (index > -1) {
            alreadySelected.selected.splice(index, 1);
            localStorage.setItem('selected_recipes', JSON.stringify(alreadySelected));
        }
    });
});
