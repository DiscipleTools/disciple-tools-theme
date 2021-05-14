/**
 * Setup the feature tour using Shepherd
 * https://shepherdjs.dev/docs
 */

const tour = new Shepherd.Tour({
    defaultStepOptions: {
        classes: 'shadow-md bg-purple-dark',
        //        scrollTo: true
    },
    useModalOverlay: true,
});

const escapeObject = window.SHAREDFUNCTIONS.escapeObject

const {
    done: doneLabel,
    next: nextLabel,
    back: backLabel,
    close_tour: closeTourLabel,
} = escapeObject(window.wpApiShare.translations)

const {
    create_post_tour,
    filter_posts_tour,
    view_posts_tour,
} = escapeObject(window.list_settings.translations)

function createStep(selector, position, shepherdOptions = {}, options = {}) {
    const defaultOptions = {
        firstStep: false,
    }

    const opts = { ...defaultOptions, ...options }

    tour.addStep({
        attachTo: {
            element: selector,
            on: position,
        },
        cancelIcon: {
            enabled: true,
            label: closeTourLabel,
        },
        beforeShowPromise: function () {
            return new Promise(function (resolve) {
                const element = document.querySelector(selector)
                if (!element) {
                    return
                }
                resolve()
            })
        },
        ...shepherdOptions,
        buttons: [
            {
                text: opts.firstStep ? doneLabel : backLabel,
                action: opts.firstStep ? tour.cancel : tour.back
            },
            {
                text: nextLabel,
                action: tour.next,
            }
        ],
    })
}

createStep('.create-post-desktop', 'bottom', {
    id: 'create-contact',
    text: create_post_tour,
}, {
    firstStep: true,
});

createStep('.filter-posts-desktop', 'bottom', {
    id: 'filter-contacts',
    text: filter_posts_tour,
});

createStep('#records-table', 'top-start', {
    id: 'view-contacts',
    text: view_posts_tour,
});

tour.start()