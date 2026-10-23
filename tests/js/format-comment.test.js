import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

// Load the production helpers without running page initialization or requiring
// WordPress, jQuery, or a live site.
const source = readFileSync(
  new URL('../../dt-assets/js/shared-functions.js', import.meta.url),
  'utf8',
);
const context = vm.createContext({
  URL,
  window: {
    wpApiShare: { site_url: 'https://dt.example/site', post_type: 'contacts' },
  },
});
vm.runInContext(
  source.slice(
    source.indexOf('window.SHAREDFUNCTIONS = {'),
    source.indexOf('\nlet date_ranges ='),
  ),
  context,
);
const { formatComment, escapeHTML } = context.window.SHAREDFUNCTIONS;

test('escapes raw markup without double encoding WordPress entities', () => {
  const text = `<img src=x onerror="alert(1)"> & 'quotes' &#34; &amp;`;
  assert.equal(
    formatComment(text),
    '&lt;img src=x onerror=&quot;alert(1)&quot;&gt; &amp; &apos;quotes&apos; &#34; &amp;',
  );
  assert.equal(
    formatComment('Tom &amp; Jerry &amp;lt;tag&amp;gt;'),
    'Tom &amp; Jerry &amp;lt;tag&amp;gt;',
  );
  assert.equal(
    formatComment('[Tom &amp; Jerry](https://example.org/?a=1&amp;b=2)'),
    '<a href="https://example.org/?a=1&amp;b=2">Tom &amp; Jerry</a>',
  );
  assert.equal(
    formatComment('![&quot; onload=marker](https://example.org/?x=&apos;)'),
    '<img src="https://example.org/?x=&apos;" alt="&quot; onload=marker">',
  );
});

test('escapes image URLs and alt text at their attribute boundaries', () => {
  const url = `https://example.org/image.png?' onerror='alert&x="y"`;
  const alt = `portrait' onload='alert <tag> & "name"`;
  assert.equal(
    formatComment(`![${alt}](${url})`),
    `<img src="${escapeHTML(url)}" alt="${escapeHTML(alt)}">`,
  );
});

test('escapes markdown link destinations and labels', () => {
  const url = 'https://example.org/" onmouseover="alert?x=1&y=2';
  const label = `<svg onload="alert"> O'Brien & friends`;
  assert.equal(
    formatComment(`[${label}](${url})`),
    `<a href="${escapeHTML(url)}">${escapeHTML(label)}</a>`,
  );
});

test('leaves unsafe and malformed image/link destinations as escaped text', () => {
  for (const url of [
    'javascript:alert',
    'javascript:alert//https://example.org',
    'data:text/html,<svg onload=alert>',
    'vbscript:alert',
    'file:///tmp/image.png',
    '//example.org/image.png',
    'https://',
  ]) {
    for (const prefix of ['!', '']) {
      const input = `${prefix}[label](${url})`;
      assert.equal(formatComment(input), escapeHTML(input), input);
    }
  }
});

test('preserves HTTP and HTTPS images, external links, and record links', () => {
  assert.equal(
    formatComment('![photo](http://example.org/photo.png)'),
    '<img src="http://example.org/photo.png" alt="photo">',
  );
  assert.equal(
    formatComment('[Website](https://example.org/?a=1&b=2)'),
    '<a href="https://example.org/?a=1&amp;b=2">Website</a>',
  );
  assert.equal(
    formatComment('[Alice](123)'),
    '<a href="https://dt.example/site/contacts/123">Alice</a>',
  );
  assert.equal(
    formatComment('[https://example.org](Website)'),
    '<a href="https://example.org">Website</a>',
  );
});

test('preserves multilingual mentions and emphasis while escaping their text', () => {
  assert.equal(
    formatComment('@[**علي** & <name>](42)'),
    '<a dir="auto">@<strong>علي</strong> &amp; &lt;name&gt;</a>',
  );
  assert.equal(
    formatComment('**bold** __also bold__ *italic*'),
    '<strong>bold</strong> <strong>also bold</strong> <em>italic</em>',
  );
  assert.equal(
    formatComment('[**Website**](https://example.org)'),
    '<a href="https://example.org"><strong>Website</strong></a>',
  );
});

test('does not reparse generated attributes or URLs as markdown', () => {
  const url = 'https://example.org/**path**?q=__value__';
  assert.equal(
    formatComment(`![**literal alt**](${url}) [link](${url})`),
    `<img src="${url}" alt="**literal alt**"> <a href="${url}">link</a>`,
  );
});

test('autolinks domains and query strings, leaves email alone, and works beside images', () => {
  assert.equal(
    formatComment('https://example.org/?a=1&b=2'),
    '<a href="https://example.org/?a=1&amp;b=2" rel="noopener noreferrer" target="_blank">https://example.org/?a=1&amp;b=2</a>',
  );
  assert.equal(formatComment('alice@example.org'), 'alice@example.org');
  assert.equal(
    formatComment('![photo](https://example.org/photo.png) www.example.org'),
    '<img src="https://example.org/photo.png" alt="photo"> <a href="http://www.example.org" rel="noopener noreferrer" target="_blank">www.example.org</a>',
  );
});

test('preserves list formatting and escapes list contents', () => {
  assert.match(
    formatComment('- **first**\n- <unsafe>'),
    /^<ul><li><strong>first<\/strong><\/li>\n<li>&lt;unsafe&gt;/,
  );
  assert.match(formatComment('1. first\n2. second'), /^<ol><li>1\. first/);
});

test('renders the HTML tags WordPress allows in stored comments', () => {
  assert.equal(
    formatComment(
      'ALERT: duplicate. <a href="https://dt.example/site/contacts/12">Jane &amp; Co</a>',
    ),
    'ALERT: duplicate. <a href="https://dt.example/site/contacts/12">Jane &amp; Co</a>',
  );
  assert.equal(
    formatComment(
      "Line<br>two<BR /> <STRONG>bold</STRONG> <em>it</em> <a title='O\"B' href='https://example.org/?a=1&amp;b=2'>x</a>",
    ),
    'Line<br>two<br> <strong>bold</strong> <em>it</em> <a title="O&quot;B" href="https://example.org/?a=1&amp;b=2">x</a>',
  );
});

test('escapes allowed tags with unsafe or unexpected attributes', () => {
  for (const input of [
    '<a href="javascript:alert(1)">x</a>',
    '<a href="//example.org">x</a>',
    '<a href="https://example.org" onclick="alert(1)">x</a>',
    '<strong class="x">x</strong>',
    '<abbr>x</abbr>',
  ]) {
    assert.ok(
      formatComment(input).startsWith(escapeHTML(input).slice(0, 12)),
      input,
    );
  }
});

test('never places generated markup inside rebuilt tag attributes', () => {
  const input =
    '<a href="https://example.org" title="[x](https://b.example/ onmouseover=alert(1))">y</a>';
  assert.ok(!formatComment(input).startsWith('<a href="https://example.org"'));
  assert.ok(
    !formatComment(
      '![<a href="https://x.example/ onerror=alert(1) ">](https://example.org/i.png)',
    ).includes('alt="<a'),
  );
});

test('keeps input from impersonating generated markup placeholders', () => {
  assert.equal(
    formatComment('\u00000\u0000 [safe](https://example.org)'),
    '0 <a href="https://example.org">safe</a>',
  );
});

test('preserves empty input and can repeatedly render the same raw activity note', () => {
  for (const input of ['', undefined, null]) {
    assert.equal(formatComment(input), input);
  }
  const note = "Changed to **O'Brien & sons**";
  const expected = 'Changed to <strong>O&apos;Brien &amp; sons</strong>';
  assert.equal(formatComment(note), expected);
  assert.equal(formatComment(note), expected);
});

test('record activity can rerender without mutating or double escaping its source', () => {
  const commentsSource = readFileSync(
    new URL('../../dt-assets/js/comments.js', import.meta.url),
    'utf8',
  );
  const start = commentsSource.indexOf('  function display_activity_comment()');
  const end = commentsSource.indexOf('\n  function emojiButtons()', start);
  const date = { diff: () => 0 };
  const rawNote = "Updated **O'Brien & sons**: [Alice](123) {123}";
  const activity = Object.freeze({ object_note: rawNote, date, name: 'Admin' });
  const rendered = [];
  const wrapper = {
    empty: () => {
      rendered.length = 0;
    },
    append: (value) => rendered.push(value),
  };
  context.window.SHAREDFUNCTIONS.getCookie = () => '[]';
  context.window.SHAREDFUNCTIONS.formatDate = () => 'Date';
  context.window.lodash = {
    union: (first, second) => [...new Set([...first, ...second])],
    first: (array) => array[0],
  };
  const displayContext = vm.createContext({
    window: context.window,
    document: { querySelectorAll: () => [] },
    $: () => wrapper,
    moment: () => ({ unix: () => 0 }),
    activity: [activity],
    comments: [{ comment_content: 'A & B', date, comment_author: 'Admin' }],
    commentsSettings: {},
    formatComment,
    formatTimestampToDate: () => 'Date',
    formatTimestampToDateTime: () => 'Date and time',
    commentTemplate: (data) => data,
  });
  vm.runInContext(
    `'use strict';\n${commentsSource.slice(start, end)}`,
    displayContext,
  );
  for (let i = 0; i < 2; i++) {
    vm.runInContext('display_activity_comment()', displayContext);
    assert.equal(activity.object_note, rawNote);
    assert.equal(
      rendered[0].activity.find((item) => !item.comment).text,
      'Updated <strong>O&apos;Brien &amp; sons</strong>: <a href="https://dt.example/site/contacts/123">Alice</a> Date',
    );
    assert.equal(
      rendered[0].activity.find((item) => item.comment).text,
      'A &amp; B',
    );
  }
});

test('grouped activity comments are escaped exactly once, including dated notes', () => {
  const activitySource = readFileSync(
    new URL('../../dt-assets/js/activity-log.js', import.meta.url),
    'utf8',
  );
  const start = activitySource.indexOf('  function makeGroupedActivitiesHtml(');
  const end = activitySource.indexOf(
    '\n  function groupActivityByDayAndRecord(',
    start,
  );
  const activityContext = vm.createContext({
    window: context.window,
    timestamptoDate: () => 'Date',
    groupActivityTypes: ([action]) => ({
      [action]: {
        fields: [],
        object_notes: ["**O'Brien & sons** <unsafe> {123}"],
      },
    }),
  });
  vm.runInContext(activitySource.slice(start, end), activityContext);
  const render = (action) =>
    vm.runInContext(
      `makeGroupedActivitiesHtml(['${action}'], {more: 'More', less: 'Less'}, false)`,
      activityContext,
    );
  assert.ok(
    render('comment').includes(
      '<li><strong>O&apos;Brien &amp; sons</strong> &lt;unsafe&gt; Date</li>',
    ),
  );
  assert.ok(
    render('other').includes(
      '<li>**O&apos;Brien &amp; sons** &lt;unsafe&gt; Date</li>',
    ),
  );
});
