'use strict';

var location = require("../../../js/location/location");

describe('path.decode', function () {
  it('properly decodes a path with a query string', function () {
    expect(location.path.decode('/my/short+path?a=b&c=d')).toEqual('/my/short path?a=b&c=d');
  });
});

describe('location.path.encode', function () {
  it('properly encodes a path with a query string', function () {
    expect(location.path.encode('/my/short path?a=b&c=d')).toEqual('/my/short+path?a=b&c=d');
  });
});

describe('location.path.extractParamNames', function () {
  describe('when a pattern contains no dynamic segments', function () {
    it('returns an empty array', function () {
      expect(location.path.extractParamNames('a/b/c')).toEqual([]);
    });
  });

  describe('when a pattern contains :a and :b dynamic segments', function () {
    it('returns the correct names', function () {
      expect(location.path.extractParamNames('/comments/:a/:b/edit')).toEqual([ 'a', 'b' ]);
    });
  });

  describe('when a pattern has a *', function () {
    it('uses the name "splat"', function () {
      expect(location.path.extractParamNames('/files/*.jpg')).toEqual([ 'splat' ]);
    });
  });
});

describe('location.path.extractParams', function () {
  describe('when a pattern does not have dynamic segments', function () {
    var pattern = 'a/b/c';

    describe('and the path matches', function () {
      it('returns an empty object', function () {
        expect(location.path.extractParams(pattern, pattern)).toEqual({});
      });
    });

    describe('and the path does not match', function () {
      it('returns null', function () {
        expect(location.path.extractParams(pattern, 'd/e/f')).toBe(null);
      });
    });
  });

  describe('when a pattern has dynamic segments', function () {
    var pattern = 'comments/:id.:ext/edit';

    describe('and the path matches', function () {
      it('returns an object with the params', function () {
        expect(location.path.extractParams(pattern, 'comments/abc.js/edit')).toEqual({ id: 'abc', ext: 'js' });
      });
    });

    describe('and the pattern is optional', function () {
      var pattern = 'comments/:id?/edit';

      describe('and the path matches with supplied param', function () {
        it('returns an object with the params', function () {
          expect(location.path.extractParams(pattern, 'comments/123/edit')).toEqual({ id: '123' });
        });
      });

      describe('and the path matches without supplied param', function () {
        it('returns an object with an undefined param', function () {
          expect(location.path.extractParams(pattern, 'comments//edit')).toEqual({ id: undefined });
        });
      });
    });

    describe('and the pattern and forward slash are optional', function () {
      var pattern = 'comments/:id?/?edit';

      describe('and the path matches with supplied param', function () {
        it('returns an object with the params', function () {
          expect(location.path.extractParams(pattern, 'comments/123/edit')).toEqual({ id: '123' });
        });
      });

      describe('and the path matches without supplied param', function () {
        it('returns an object with an undefined param', function () {
          expect(location.path.extractParams(pattern, 'comments/edit')).toEqual({ id: undefined });
        });
      });
    });

    describe('and the path does not match', function () {
      it('returns null', function () {
        expect(location.path.extractParams(pattern, 'users/123')).toBe(null);
      });
    });

    describe('and the path matches with a segment containing a .', function () {
      it('returns an object with the params', function () {
        expect(location.path.extractParams(pattern, 'comments/foo.bar/edit')).toEqual({ id: 'foo', ext: 'bar' });
      });
    });
  });

  describe('when a pattern has characters that have special URL encoding', function () {
    var pattern = 'one, two';

    describe('and the path matches', function () {
      it('returns an empty object', function () {
        expect(location.path.extractParams(pattern, 'one, two')).toEqual({});
      });
    });

    describe('and the path does not match', function () {
      it('returns null', function () {
        expect(location.path.extractParams(pattern, 'one two')).toBe(null);
      });
    });
  });

  describe('when a pattern has dynamic segments and characters that have special URL encoding', function () {
    var pattern = '/comments/:id/edit now';

    describe('and the path matches', function () {
      it('returns an object with the params', function () {
        expect(location.path.extractParams(pattern, '/comments/abc/edit now')).toEqual({ id: 'abc' });
      });
    });

    describe('and the path does not match', function () {
      it('returns null', function () {
        expect(location.path.extractParams(pattern, '/users/123')).toBe(null);
      });
    });
  });

  describe('when a pattern has a *', function () {
    describe('and the path matches', function () {
      it('returns an object with the params', function () {
        expect(location.path.extractParams('/files/*', '/files/my/photo.jpg')).toEqual({ splat: 'my/photo.jpg' });
        expect(location.path.extractParams('/files/*', '/files/my/photo.jpg.zip')).toEqual({ splat: 'my/photo.jpg.zip' });
        expect(location.path.extractParams('/files/*.jpg', '/files/my/photo.jpg')).toEqual({ splat: 'my/photo' });
      });
    });

    describe('and the path does not match', function () {
      it('returns null', function () {
        expect(location.path.extractParams('/files/*.jpg', '/files/my/photo.png')).toBe(null);
      });
    });
  });

  describe('when a pattern has a ?', function () {
    var pattern = '/archive/?:name?';

    describe('and the path matches', function () {
      it('returns an object with the params', function () {
        expect(location.path.extractParams(pattern, '/archive')).toEqual({ name: undefined });
        expect(location.path.extractParams(pattern, '/archive/')).toEqual({ name: undefined });
        expect(location.path.extractParams(pattern, '/archive/foo')).toEqual({ name: 'foo' });
        expect(location.path.extractParams(pattern, '/archivefoo')).toEqual({ name: 'foo' });
      });
    });

    describe('and the path does not match', function () {
      it('returns null', function () {
        expect(location.path.extractParams(pattern, '/archiv')).toBe(null);
      });
    });
  });

  describe('when a param has dots', function () {
    var pattern = '/:query/with/:domain';

    describe('and the path matches', function () {
      it('returns an object with the params', function () {
        expect(location.path.extractParams(pattern, '/foo/with/foo.app')).toEqual({ query: 'foo', domain: 'foo.app' });
        expect(location.path.extractParams(pattern, '/foo.ap/with/foo')).toEqual({ query: 'foo.ap', domain: 'foo' });
        expect(location.path.extractParams(pattern, '/foo.ap/with/foo.app')).toEqual({ query: 'foo.ap', domain: 'foo.app' });
      });
    });

    describe('and the path does not match', function () {
      it('returns null', function () {
        expect(location.path.extractParams(pattern, '/foo.ap')).toBe(null);
      });
    });
  });
});

describe('location.path.injectParams', function () {
  describe('when a pattern does not have dynamic segments', function () {
    var pattern = 'a/b/c';

    it('returns the pattern', function () {
      expect(location.path.injectParams(pattern, {})).toEqual(pattern);
    });
  });

  describe('when a pattern has dynamic segments', function () {
    var pattern = 'comments/:id/edit';

    describe('and a param is missing', function () {
      it('throws an Error', function () {
        expect(function () {
          location.path.injectParams(pattern, {});
        }).toThrow('Missing "id" parameter for path "comments/:id/edit"');
      });
    });

    describe('and a param is optional', function () {
      var pattern = 'comments/:id?/edit';

      it('returns the correct path when param is supplied', function () {
        expect(location.path.injectParams(pattern, { id:'123' })).toEqual('comments/123/edit');
      });

      it('returns the correct path when param is not supplied', function () {
        expect(location.path.injectParams(pattern, {})).toEqual('comments//edit');
      });
    });

    describe('and a param and forward slash are optional', function () {
      var pattern = 'comments/:id?/?edit';

      it('returns the correct path when param is supplied', function () {
        expect(location.path.injectParams(pattern, { id:'123' })).toEqual('comments/123/edit');
      });

      it('returns the correct path when param is not supplied', function () {
        expect(location.path.injectParams(pattern, {})).toEqual('comments/edit');
      });
    });

    describe('and all params are present', function () {
      
      it('returns the correct path', function () {
        expect(location.path.injectParams(pattern, { id: 'abc' })).toEqual('comments/abc/edit');
      });
      
      it('returns the correct path when the value is 0', function () {
        expect(location.path.injectParams(pattern, { id: 0 })).toEqual('comments/0/edit');
      });
    });

    describe('and some params have special URL encoding', function () {
      it('returns the correct path', function () {
        expect(location.path.injectParams(pattern, { id: 'one, two' })).toEqual('comments/one, two/edit');
      });
    });

    describe('and a param has a forward slash', function () {
      it('preserves the forward slash', function () {
        expect(location.path.injectParams(pattern, { id: 'the/id' })).toEqual('comments/the/id/edit');
      });
    });

    describe('and some params contain dots', function () {
      it('returns the correct path', function () {
        expect(location.path.injectParams(pattern, { id: 'alt.black.helicopter' })).toEqual('comments/alt.black.helicopter/edit');
      });
    });
  });

  describe('when a pattern has one splat', function () {
    it('returns the correct path', function () {
      expect(location.path.injectParams('/a/*/d', { splat: 'b/c' })).toEqual('/a/b/c/d');
    });
  });

  describe('when a pattern has multiple splats', function () {
    it('returns the correct path', function () {
      expect(location.path.injectParams('/a/*/c/*', { splat: [ 'b', 'd' ] })).toEqual('/a/b/c/d');
    });

    it('complains if not given enough splat values', function () {
      expect(function () {
        location.path.injectParams('/a/*/c/*', { splat: [ 'b' ] });
      }).toThrow('Missing splat #2 for path "/a/*/c/*"');
    });
  });

  describe('when a pattern has dots', function () {
    it('returns the correct path', function () {
      expect(location.path.injectParams('/foo.bar.baz')).toEqual('/foo.bar.baz');
    });
  });
});

describe('location.path.extractQuery', function () {
  describe('when the path contains a query string', function () {
    it('returns the parsed query object', function () {
      expect(location.path.extractQuery('/?id=def&show=true')).toEqual({ id: 'def', show: 'true' });
    });

    it('properly handles arrays', function () {
      expect(location.path.extractQuery('/?id%5B%5D=a&id%5B%5D=b')).toEqual({ id: [ 'a', 'b' ] });
    });

    it('properly handles encoded ampersands', function () {
      expect(location.path.extractQuery('/?id=a%26b')).toEqual({ id: 'a&b' });
    });
  });

  describe('when the path does not contain a query string', function () {
    it('returns null', function () {
      expect(location.path.extractQuery('/a/b/c')).toBe(null);
    });
  });
});

describe('location.path.withoutQuery', function () {
  it('removes the query string', function () {
    expect(location.path.withoutQuery('/a/b/c?id=def')).toEqual('/a/b/c');
  });
});

describe('location.path.withQuery', function () {
  it('appends the query string', function () {
    expect(location.path.withQuery('/a/b/c', { id: 'def' })).toEqual('/a/b/c?id=def');
  });

  it('merges two query strings', function () {
    expect(location.path.withQuery('/path?a=b', { c: [ 'd', 'e' ]})).toEqual('/path?a=b&c%5B%5D=d&c%5B%5D=e');
  });
});

describe('location.path.normalize', function () {
  describe('on a path with no slashes at the beginning', function () {
    it('adds a slash', function () {
      expect(location.path.normalize('a/b/c')).toEqual('/a/b/c');
    });
  });

  describe('on a path with a single slash at the beginning', function () {
    it('preserves the slash', function () {
      expect(location.path.normalize('/a/b/c')).toEqual('/a/b/c');
    });
  });

  describe('on a path with many slashes at the beginning', function () {
    it('reduces them to a single slash', function () {
      expect(location.path.normalize('//a/b/c')).toEqual('/a/b/c');
    });
  });
});