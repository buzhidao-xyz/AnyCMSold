namespace php SearchThrift

struct SearchRequest {
	1 : string Keyword
}

struct SearchResponse {
	1 : string ErrorCode
}

service SearchService {
	SearchResponse Search(1:SearchRequest Request)
}